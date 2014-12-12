Admin.ServerMeetingsController = Ember.ObjectController.extend({
  actions: {
    reloadMeetings: function() {
        this.set('content', this.store.find('meeting',{ server_id: serverModel.get('id') }));
    }
  }
});