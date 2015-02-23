Admin.ServerController = Ember.ObjectController.extend({
  isEditing: false,
  isUpdating: true,
  init: function(){
	this.updateUpStatusServer();
  },
  updateUpStatusServer: function() {
	var server = this.get('model');
	if(server){
		var controller = this;
		$.ajax({
		  dataType: "json",
		  url: host_path+"/servers/"+server.get('id')+"/up",
		  headers: {"API-key":api_key}
		}).done(function(data) {
			server.set('up', data.up);
			controller.set('isUpdating', false);
		});
	}
  },
  actions: {
    toggleEditServer: function() {
		if(this.get('isEditing') == true){
			this.set('isEditing', false);
		}
		else {
			this.set('isEditing', true);
		}
    },
    reloadMeetings: function() {
    	serverModel = this.get('model');
    	this.propertyWillChange('model');
        this.set('meeting', this.store.find('meeting',{ server_id: serverModel.get('id') }));
        this.propertyDidChange('model');
    },
    saveServer: function() {
    	var controller = this;
    	var onSuccess = function(server) {
    		noty({ text: 'Saved', timeout: 5000, type: "success" });
    		controller.set('isEditing', false);
    		controller.set('isUpdating', true);
        	controller.updateUpStatusServer();
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