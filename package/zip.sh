targetPath="/opt/lampp/htdocs/Projects/distribution/GforceSaas/Application/"
rm /opt/lampp/htdocs/Projects/distribution/GforceSaas/app.zip
rm /opt/lampp/htdocs/Projects/distribution/GforceSaas/GforceSaas.zip
cd $targetPath
zip -r app.zip .
mv app.zip ../
cd ../
zip -r GforceSaas.zip app.zip Documentation Resources
rm app.zip
nautilus .
