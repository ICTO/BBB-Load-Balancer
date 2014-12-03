Admin.EditUserFirstNameView = Ember.TextField.extend({
  didInsertElement: function() {
    this.$().focus();
  }
});
Ember.Handlebars.helper('edit-user-firstname', Admin.EditUserFirstNameView);

Ember.Handlebars.registerBoundHelper('formatDate', function(date, format) {
	console.log(date);
  return moment(date).format(format);
});