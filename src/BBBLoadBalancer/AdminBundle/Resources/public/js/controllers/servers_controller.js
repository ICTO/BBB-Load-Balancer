Admin.IndexServersController = Ember.ObjectController.extend({
  newName: '',
  newURL: '',
  newEnabled: true,
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
    addServer: function() {
      var server = this.store.createRecord('server', {
        name: this.get('newName'),
        url: this.get('newURL'),
        up: false,
        enabled: this.get('newEnabled')
      });

      var controller = this;

      var onSuccess = function(server) {
        noty({ text: 'Saved', timeout: 5000, type: "success" });
        controller.set('newName', '');
        controller.set('newURL', '');
        controller.set('newEnabled', '');
        controller.set('isAdding', false);
      };

      var onFail = function(xhr) {
        var error = JSON.parse(xhr.responseText);
        noty({text: (error.get('firstObject').message), type: "error"});
        server.rollback();
      };
      server.save().then(onSuccess, onFail);
    }
  }
});