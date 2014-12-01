Admin.Server = DS.Model.extend({
  name: DS.attr('string'),
  url: DS.attr('string'),
  up: DS.attr('boolean'),
  enabled: DS.attr('boolean')
});