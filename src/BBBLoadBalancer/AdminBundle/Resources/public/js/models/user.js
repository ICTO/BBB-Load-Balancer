Admin.User = DS.Model.extend({
  firstName: DS.attr('string'),
  lastName: DS.attr('string'),
  email: DS.attr('string'),
  apiKey: DS.attr('string'),
  timezone: DS.attr('string'),
  password1: DS.attr('string'),
  password2: DS.attr('string')
});