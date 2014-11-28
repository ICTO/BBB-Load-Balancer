Admin.EditUserFirstNameView = Ember.TextField.extend({
  didInsertElement: function() {
    this.$().focus();
  }
});

Ember.Handlebars.helper('edit-user-firstname', Admin.EditUserFirstNameView);