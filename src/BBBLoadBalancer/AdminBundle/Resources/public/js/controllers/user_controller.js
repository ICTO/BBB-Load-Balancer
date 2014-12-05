Admin.UserController = Ember.ObjectController.extend({
  isEditing: false,
  actions: {
    toggleEditUser: function() {
		if(this.get('isEditing') == true){
			this.set('isEditing', false);
		}
		else {
			this.set('isEditing', true);
		}
    },
    saveUser: function() {
    	var controller = this;
    	var onSuccess = function(user) {
    		noty({ text: 'Saved', timeout: 5000, type: "success" });
    		controller.set('isEditing', false);
		};

		var onFail = function(xhr) {
		    var error = JSON.parse(xhr.responseText);
		    noty({text: (error.get('firstObject').message), type: "error"});
		};
        this.get('model').save().then(onSuccess, onFail);
	},
	removeUser: function () {
		var controller = this;
		var user = this.get('model');

    	var onSuccess = function(user) {
    		noty({ text: 'Removed', timeout: 5000, type: "success" });
    		controller.set('isEditing', false);
		};

		var onFail = function(xhr) {
		    var error = JSON.parse(xhr.responseText);
		    noty({text: (error.get('firstObject').message), type: "error"});
		    user.rollback();
		};

		user.deleteRecord();
		user.save().then(onSuccess, onFail);
	}
  },
});