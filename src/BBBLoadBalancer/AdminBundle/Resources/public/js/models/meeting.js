Admin.Meeting = DS.Model.extend({
  name: DS.attr('string'),
  created: DS.attr('date'),
  running: DS.attr('boolean')
});