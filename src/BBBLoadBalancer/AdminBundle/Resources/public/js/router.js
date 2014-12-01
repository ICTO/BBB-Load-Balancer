Admin.Router.map(function() {
  this.resource('index', { path: '/' }, function () {
    this.route('users', { path: '/users' });
    this.route('servers', { path: '/servers' });
  });
});

Admin.IndexUsersRoute = Ember.Route.extend({
  model: function() {
    return this.store.find('user');
  }
});

Admin.IndexServersRoute = Ember.Route.extend({
  model: function() {
    return this.store.find('server');
  }
});

Admin.IndexRoute = Ember.Route.extend({
  model: function() {
    return this.store.find('user',{ active: true }).then(function (list) {
       return list.get('firstObject');
    });
  },
  redirect: function() {
    this.transitionTo('index.servers');
  }
});