server 'us-east-1.erphost.p-e-p.com',
  user: 'deploy',
  roles: %w{web app},
  ssh_options: {
    keys: %w(~/.ssh/id_rsa),
    forward_agent: true,
    auth_methods: %w(publickey)
  }
