targetPath="/opt/lampp/htdocs/Projects/distribution/GforceSaas/Application"
cd $targetPath
rm -rf pakcage/
rm -rf saas/
rm -rf storage/
mkdir "storage"
mkdir "storage/logs"
mkdir "storage/app"
mkdir "storage/framework"
mkdir "storage/framework/sessions"
mkdir "storage/framework/cache"
mkdir "storage/framework/views"
mkdir "storage/tmp"
find ./storage -type d -exec chmod 777 {} \;
find ./bootstrap -type d -exec chmod 777 {} \;
find ./resources/lang -type d -exec chmod 777 {} \;
#chmod 777 storage
#chmod 777 storage/logs
rm saas.sh
rm package.sh
rm readme.md
rm -rf public/uploads/*
chmod 777 public/uploads
rm -rf node_modules/
rm -rf nbproject/
#delete language files
find public/themes/ -type f -name "*.html" -delete
find public/themes/ -type f -name "*.txt" -delete
find ./ -type f -name "README.md" -delete
find ./ -type f -name "readme.md" -delete
find vendor/ -type f -name "*.txt" -delete
find public/ -type f -name "*.html" -delete
#rm .env
#cp /opt/lampp/htdocs/Projects/gforce/storage/.env $targetPath
chmod 777 .env
