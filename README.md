### How to run Project?

- Setup database
- Run migration 
- Create .env.example file into "admin" folder. Add Following variables there:
    
    VUE_APP_API_URL=http://YOURURL
    
    VUE_APP_STORAGE_PREFIX=il_
    
    VUE_APP_ENV=local

- Run "npm install" in admin folder
- Run "npm run serve" in admin folder
- To allow the access from API domain remove comment in App\Http\Kernel.php line number 25 \Fruitcake\Cors\HandleCors::class
