targetPath="/opt/lampp/htdocs/Projects/distribution/GforceSaas/Application"
cd $targetPath
#set permissions
echo "Setting permissions, please wait..."
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
