# config valid only for current version of Capistrano
lock '3.4.0'

# Symfony log path
set :log_path,              fetch(:app_path) + "/logs"

# Symfony cache path
set :cache_path,            fetch(:app_path) + "/cache"

# Symfony config file path
set :app_config_path,       fetch(:app_path) + "/config"

set :application, 'symfony'
set :repo_url, 'git@bitbucket.org:phpgenie/erp.git'
set :branch, :master

# Default deploy_to directory is /var/www/my_app_name
set :deploy_to, '/var/www/vhosts/symfony'

# Default value for :log_level is :debug
# set :log_level, :debug

# Default value for :pty is false
# set :pty, true

# Default value for :linked_files is []
 set :linked_files, fetch(:linked_files, []).push('app/config/parameters.yml')

# Default value for linked_dirs is []
 set :linked_dirs, fetch(:linked_dirs, []).push(fetch(:log_path), fetch(:cache_path), 'vendor', 'bin')

set :composer_install_flags, '--no-interaction --optimize-autoloader'

namespace :deploy do

  after :restart, :clear_cache do
    on roles(:web), in: :groups, limit: 3, wait: 10 do
      # Here we can do anything such as:
      # within release_path do
      #   execute :rake, 'cache:clear'
      # end
    end
  end

end
