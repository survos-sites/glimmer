rm -f var/data.db & bin/console d:sch:update --force
bin/create-admins.sh

# we could create a user with Flickr data, or run a CLI program here
