Admin.Router.map(function() {
  this.resource('application', { path: '/' }, function () {
    this.route('users', { path: '/users' });
    this.route('api', { path: '/api-docs' });
    this.route('servers', { path: '/servers' });
    this.resource('server', { path: '/server/:id' }, function () {
      this.route('meetings', { path: '/meetings' });
    });
  });
});

Admin.UsersRoute = Ember.Route.extend({
  model: function() {
    return this.store.find('user');
  }
});

Admin.ServersRoute = Ember.Route.extend({
  model: function() {
    return this.store.find('server');
  }
});

Admin.ServerRoute = Ember.Route.extend({
  model: function(params) {
    return this.store.filter('server',{ id: params.id });
  }
});

Admin.ServerMeetingsRoute = Ember.Route.extend({
  model: function() {
    serverModel = this.modelFor('server');
    return this.store.find('meeting',{ server_id: serverModel.get('id') });
  }
});

Admin.ApplicationRoute = Ember.Route.extend({
  model: function() {
    return this.store.find('user',{ active: true }).then(function (list) {
       return list.get('firstObject');
    });
  },
  redirect: function() {
    this.transitionTo('servers');
  }
});