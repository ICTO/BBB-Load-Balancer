Admin.Router.map(function() {
  this.resource('index', { path: '/' }, function () {
    this.route('users', { path: '/users' });
  });
});

Admin.IndexUsersRoute = Ember.Route.extend({
  model: function() {
    return this.store.find('user');
  }
});

Admin.IndexRoute = Ember.Route.extend({
  model: function() {
    return this.store.find('user',{ active: true }).then(function (list) {
       return list.get('firstObject');
    });
  }
});