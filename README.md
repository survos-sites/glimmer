# Glimmer: A flickr-bundle demo

The demo integrates survos/flickr-bundle, which is a wrapper for https://github.com/samwilson/phpflickr

It also demonstrates how to deploy using symfony's secrets vault, putting only the decryption key in the environment variables of dokku.

The primary page simply displays the configured users albums.  

## Secrets



The Flickr Application name and ID are _not_ secret, so they can simply be defined in the .env file.
The API key and secret, though, which allow users to log into their flickr account, are secret, so are added to the vault.



```bash
bin/console debug:container --env-vars
bin/console debug:dotenv
bin/console secrets:list --reveal
```

## Extras

For a while, https://github.com/webelop/album-bundle was integrated, it looked promising but is now archived.

I also used bunnyCDN to transferring files, but now that is in https://github.com/survos-sites/bunny-demo

