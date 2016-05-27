server 'myhostname',
  user: 'deploy',
  roles: %w{web app},
  ssh_options: {
    keys: %w(~/.ssh/id_rsa),
    forward_agent: true,
    auth_methods: %w(publickey)
  }
