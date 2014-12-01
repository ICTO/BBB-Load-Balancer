Admin.IndexUsersController = Ember.ObjectController.extend({
  newFirstName: '',
  newLastName: '',
  newEmail: '',
  newTimezone: 'Europe/Brussels',
  newPlainPassword1: '',
  newPlainPassword2: '',
  isAdding: false,
  actions: {
    toggleIsAdding: function() {
      if(this.get('isAdding') == true){
        this.set('isAdding', false);
      }
      else {
        this.set('isAdding', true);
      }
    },
    addUser: function() {
      var firstName = this.get('newFirstName');
      var lastName = this.get('newLastName');
      var email = this.get('newEmail');
      var timezone = this.get('newTimezone');
      var plainPassword1 = this.get('newPlainPassword1');
      var plainPassword2 = this.get('newPlainPassword2');
      var user = this.store.createRecord('user', {
        firstName: firstName,
        lastName: lastName,
        email: email,
        timezone: timezone,
        password1 : plainPassword1,
        password2 : plainPassword2,
      });

      var controller = this;

      var onSuccess = function(user) {
        noty({ text: 'Saved', timeout: 5000, type: "success" });
        controller.set('newFirstName', '');
        controller.set('newLastName', '');
        controller.set('newEmail', '');
        controller.set('newTimezone', 'Europe/Brussels');
        controller.set('newPlainPassword1', '');
        controller.set('newPlainPassword2', '');
        controller.set('isAdding', false);
      };

      var onFail = function(xhr) {
        var error = JSON.parse(xhr.responseText);
        noty({text: (error.get('firstObject').message), type: "error"});
        user.rollback();
      };
      user.save().then(onSuccess, onFail);
    }
  }
});