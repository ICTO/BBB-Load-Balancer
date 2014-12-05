Admin.ServerController = Ember.ObjectController.extend({
  isEditing: false,
  actions: {
    toggleEditServer: function() {
		if(this.get('isEditing') == true){
			this.set('isEditing', false);
		}
		else {
			this.set('isEditing', true);
		}
    },
    saveServer: function() {
    	var controller = this;
    	var onSuccess = function(server) {
    		noty({ text: 'Saved', timeout: 5000, type: "success" });
    		controller.set('isEditing', false);
		};

		var onFail = function(xhr) {
		    var error = JSON.parse(xhr.responseText);
		    noty({text: (error.get('firstObject').message), type: "error"});
		};
        this.get('model').save().then(onSuccess, onFail);
	},
	removeServer: function () {
		var controller = this;
		var server = this.get('model');

    	var onSuccess = function(server) {
    		noty({ text: 'Removed', timeout: 5000, type: "success" });
    		controller.set('isEditing', false);
		};

		var onFail = function(xhr) {
		    var error = JSON.parse(xhr.responseText);
		    noty({text: (error.get('firstObject').message), type: "error"});
		    user.rollback();
		};

		server.deleteRecord();
		server.save().then(onSuccess, onFail);
	}
  }
});