window.Admin = Ember.Application.create();

Admin.ApplicationAdapter = DS.RESTAdapter.extend({
  host: host_path,
  headers: {
    "API-key": api_key,
  }
});