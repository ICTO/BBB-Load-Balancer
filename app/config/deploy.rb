# deploy.rb

set   :application,   "BBB Load Balancer"
set   :deploy_to,     "/var/www/BBBLoadBalancer"
set   :domain,        "localhost"

set   :scm,           :none
set   :repository,    "."
set   :deploy_via,    :copy

role  :web,           domain
role  :app,           domain, :primary => true
role  :db,            domain, :primary => true

set   :user,          "root"
set   :use_sudo,      false
set   :keep_releases, 5

after "deploy", "deploy:cleanup"

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs", web_path + "/uploads", "vendor"]

set :use_composer, true
set :update_vendors, false

set :writable_dirs,       ["app/cache", "app/logs"]
set :webserver_user,      "www-data"
set :permission_method,   :acl
set :use_set_permissions, true

set :copy_vendors, false

set :model_manager, "doctrine"

set :dump_assetic_assets, true

# namespace :deploy do
#   task :restart do
#     run "service php5-fpm restart"
#   end
# end
