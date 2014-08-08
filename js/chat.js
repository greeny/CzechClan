$(document).ready(function() {
	$('body').on('click', 'a[data-link]', function(e) {
		chat.onClick($(this));
		e.preventDefault();
	});

	$('.debug-toggle').on('click', function() {
		$('.debug-log').toggle();
	});

	$('input').on('keydown', function(e) {
		if(e.which === 13) { // enter
			chat.submitMessage();
			e.preventDefault();
		}
	});

	$('button').on('click', function(e) {
		chat.submitMessage();
		e.preventDefault();
	});
});

var Chat = function() {
	this.version = 10000;
	this.key = undefined;
	this.pingInterval = undefined;
	this.newMessageLink = undefined;
	this.user = undefined;
	this.currentTab = 'rooms';
	this.running = false;
	this.data = {
		rooms: {},
		users: {},
		onlineUserIds: {},
		messages: {}
	};

	this.stop = function() {
		chat.debugger.log('Stopping pings');
		clearInterval(this.pingInterval);
		chat.running = false;
	};

	this.start = function(pingLink) {
		chat.debugger.log('Starting pings');
		function f() {new Request(pingLink).setSuccessCallback(chat.onPing).send()}
		f();
		chat.pingInterval = setInterval(f, 2500);
		chat.currentTab = 'users';
		chat.renderer.initialRender();
		chat.running = true;
	};

	this.onClick = function(element) {
		if(!chat.running) return;
		var tab = chat.currentTab = element.data('link');
		chat.renderer.renderActiveTab();
		if(tab === 'rooms') {
			chat.renderer.renderRooms();
		} else if(tab === 'users') {
			chat.renderer.renderActiveUsers();
		} else if(tab.substr(0, 5) === 'room:') {
			chat.renderer.showChat();
			chat.renderer.renderAllMessages();
			var ch = $('#chat');
			ch.scrollTop(1e6);
		} else {
			chat.renderer.showChat();
		}
	};

	this.onPing = function(data) {
		chat.debugger.log('Ping', 'PING');
		chat.data.onlineUserIds = data.online_users;
		for(var k in data.messages) {
			chat.data.messages[k] = data.messages[k];
		}
		if(chat.currentTab === 'users') {
			chat.renderer.renderActiveUsers();
		} else {
			chat.renderer.renderAllMessages();
			chat.renderer.renderNewMessages(data.messages);
		}
	};

	this.submitMessage = function() {
		var input = $('input'), val = input.val();
		input.val('');

		if(val.charAt(0) === '/') {
			chat.parseCommand(val.substr(1));
		} else {
			chat.sendMessage(val);
		}
	};

	this.parseCommand = function(command) {
		var parts = command.split(' ');
		if(parts[0] === 'debug') {
			chat.debugger.enable();
		}
	};

	this.sendMessage = function(message) {
		new Request(this.newMessageLink.replace('%25s', message)).setSuccessCallback(chat.onPing).send();
	};

	this.run = function(path) {
		chat.debugger.log('Initializing chat');
		chat.renderer.setLoadingStatus('Inicializace');
		new Request(path).setSuccessCallback(function(data) {
			chat.debugger.log('Logging in');
			chat.renderer.setLoadingStatus('Přihlašování');
			new Request(data.links.chat.login).setSuccessCallback(function(data) {
				chat.debugger.log('Logged in as ' + data.session.user.nick + '(id = ' + data.session.user.id + ')');
				chat.renderer.elements.userLink
					.attr('href', data.session.user.links.profile)
					.html('<span class="icon-user"></span> ' + data.session.user.nick);
				chat.key = data.session.key;
				chat.user = data.session.user;
				chat.debugger.log('Loading messages');
				chat.renderer.setLoadingStatus('Načítání zpráv');
				new Request(data.links.chat.state).setSuccessCallback(function(data) {
					chat.debugger.log('Messages loaded');
					chat.data.rooms = data.rooms;
					chat.data.users = data.users;
					chat.data.onlineUserIds = data.online_users;
					chat.data.messages = data.messages;
					chat.newMessageLink = data.links.chat.new_message;
					chat.start(data.links.chat.ping);
				}).send();
			}).send();
		}).send();
	};


	this.debugger = {};
	this.debugger.enabled = false;
	this.debugger.element = $('.debug-log');
	this.debugger.enable = function() {
		this.enabled = true;
		$('.debug-toggle').show();
	};

	this.debugger.log = function(message, who, color) {
		color = color || 'white';
		who = who || 'LOG';
		this.element.append('<div>' + (new Date).toLocaleTimeString() +
			' [<span style="color: white">' + who + '</span>] ' +
			'<span style="color: ' + color + '">' + message + '</span></div>');
	};


	this.renderer = {};
	this.renderer.elements = {
		userLink: $('#userLink'),
		page: $('#page'),
		chat: $('#chat'),
		chatContent: $('#chat-content'),
		pageContent: $('#page-content'),
		pageContentCentered: $('#page-content-centered'),
		sidebar: {
			sidebar: $('#sidebar'),
			static: $('#room-list-static'),
			rooms: $('#room-list')
		}
	};

	this.renderer.setLoadingStatus = function(status) {
		this.elements.chat.hide();
		this.elements.pageContent.hide();
		this.elements.page.show();
		this.elements.pageContentCentered.html('<span class="icon-spin icon-spinner"></span> ' +
			'<span class="status">' + status + '</span>');
		this.elements.pageContentCentered.show();
	};

	this.renderer.setStatus = function(status) {
		this.elements.chat.hide();
		this.elements.pageContent.hide();
		this.elements.page.show();
		this.elements.pageContentCentered.html(status);
		this.elements.pageContentCentered.show();
	};

	this.renderer.hidePage = function() {
		this.elements.page.hide();
	};

	this.renderer.showChat = function() {
		this.elements.page.hide();
		this.elements.chat.show();
	};

	this.renderer.initialRender = function() {
		this.elements.sidebar.sidebar.find('li.disabled').removeClass('disabled');
		$('[data-link="users"]').parent().addClass('active');
		this.renderActiveUsers();
		this.renderSidebar();
	};

	this.renderer.renderActiveTab = function() {
		this.elements.sidebar.sidebar.find('li.active').removeClass('active');
		$('[data-link="' + chat.currentTab +'"]').parent().addClass('active');
	};

	this.renderer.renderSidebar = function() {
		var content = '';
		for(var k in chat.user.pinned_rooms) {
			content += '<li><a href="#" data-link="room:' + chat.user.pinned_rooms[k].id + '">' +
				'<span class="icon-comments"></span> ' + chat.user.pinned_rooms[k].name +
				'<span class="badge pull-right"></span></a></li>';
		}
		this.elements.sidebar.rooms.html(content);
	};

	this.renderer.renderRooms = function() {
		this.elements.chat.hide();
		var content = '<div class="page-header"><h1>Místnosti</h1></div> <ul>';
		for(var k in chat.rooms) {
			content += '<li><a href="#" data-link="room:' + chat.rooms[k].id + '">' +
				'<span class="icon-comments"></span> ' + chat.rooms[k].name + '</a></li>';
		}
		this.elements.pageContent.html(content + '</ul>');
		this.elements.pageContentCentered.hide();
		this.elements.pageContent.show();
		this.elements.page.show();
	};

	this.renderer.renderRoomMessages = function(roomId) {
		for(var k in chat.messages[roomId]) {
			this.renderMessage(roomId, chat.messages[roomId][k]);
		}
	};

	this.renderer.renderMessage = function(roomId, messageData) {
		var el = $('[data-message="' + messageData.id + '"]');
		if(el.length) {
			return;
		}
		var messages = chat.messages[roomId];
		var latest = 0;
		for(var k in messages) {
			if(messages[k].id < messageData.id && messages[k].id > latest) {
				latest = messages[k].id;
			}
		}
		if(latest !== 0) {
			$('[data-message="' + latest + '"]').append('<tr data-message="' + messageData.id + '"><td>'
				+ messageData.user.nick + '</td>' +
				'<td>' + messageData.message + '</td>' +
				'<td>' + new Date(messageData.time).toLocaleTimeString() + '</td></tr>');
		} else {
			$('#chat-content').html('<tr data-message="' + messageData.id + '"><td>'
				+ messageData.user.nick + '</td>' +
				'<td>' + messageData.message + '</td>' +
				'<td>' + new Date(messageData.time).toLocaleTimeString() + '</td></tr>');
		}
	};

	this.renderer.renderActiveUsers = function() {
		this.elements.chat.hide();
		var content = '<div class="page-header"><h1>Online uživatelé</h1></div> <ul>';
		for(var k in chat.data.onlineUserIds) {
			var id = chat.data.onlineUserIds[k];
			content += '<li><a href="' + chat.data.users[id].links.profile + '" target="_blank">' +
			// 'data-link="user:' + chat.data.users[id].id + '"' +
				'<span class="icon-user"></span> ' + chat.data.users[id].nick + '</a></li>';
		}
		this.elements.pageContent.html(content + '</ul>');
		this.elements.pageContentCentered.hide();
		this.elements.pageContent.show();
		this.elements.page.show();
	};

	this.renderer.renderAllMessages = function() {
		var arr = [];
		for(var k in chat.data.messages) {
			arr[k] = chat.data.messages[k];
		}
		arr.sort(function(a, b) {
			return a.time - b.time;
		});
		for(k in arr) {
			chat.renderer.renderSingleMessage(arr[k]);
		}
	};

	this.renderer.renderNewMessages = function(messages) {
		this.elements.chatContent.find('.new').removeClass('new');
		var counter = 0;
		for(var k in messages) {
			chat.renderer.renderSingleMessage(messages[k]);
			if(messages[k].user !== chat.user.id) {
				this.elements.chatContent.find('[data-message="' + k + '"]').addClass('new');
				counter++;
			}
		}
		if(counter > 0) {
			$('[data-message-count="global"]').html(counter);
		} else {
			$('[data-message-count="global"]').html('');
		}
	};

	this.renderer.renderSingleMessage = function(arr) {
		var el = $('[data-message="' + arr.id + '"]');
		if(el.length) {
			return;
		}
		this.elements.chatContent.append('<tr data-message="' + arr.id + '"><td><span class="icon-user"></span> '
			+ chat.data.users[arr.user].nick + '</td>' +
			'<td>' + arr.message + '</td>' +
			'<td>' + new Date(arr.time).toLocaleTimeString() + '</td></tr>');
		var ch = $('#chat');
		console.log(ch.scrollTop());
		ch.scrollTop(ch.scrollTop() + 30);
	}
};

var Request = function(path) {
	var params = {}, asynchronous = true;
	var successCallback = function() {};
	var errorCallback = function() {};

	this.checkVersion = function(data) {
		chat.debugger.log('Recieved response', 'AJAX');
		if(data.errors.length) {
			chat.renderer.setStatus(data.errors[0]);
		} else {
			successCallback(data);
		}
	};

	this.setParam = function(key, value) {
		params[key] = value;
		return this;
	};

	this.setSuccessCallback = function(callback) {
		successCallback = callback;
		return this;
	};

	this.setErrorCallback = function(callback) {
		errorCallback = callback;
		return this;
	};

	this.setAsynchronous = function(s) {
		asynchronous = false;
	};

	this.send = function() {
		if(chat.key) {
			this.setParam('key', chat.key);
		}
		chat.debugger.log('Sending request: ' + path, 'AJAX');
		var xhr = $.ajax({
			url: path,
			type: 'get',
			dataType: 'json',
			async: asynchronous,
			data: params,
			cache: false,
			statusCode: {
				500: function() {
					chat.stop();
					chat.renderer.setStatus('Na stránce se vyskytla chyba, zkuste to prosím znovu nebo kontaktuje administrátora.');
				},
				404: function() {
					chat.stop();
					chat.renderer.setStatus('API je nedostupná, zkuste to prosím později.');
				}
			}
		}).done(this.checkVersion).error(errorCallback);
	};
};

chat = new Chat();
