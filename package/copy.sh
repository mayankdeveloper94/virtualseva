targetPath="/opt/lampp/htdocs/Projects/distribution/GforceSaas/Application"
#copy entire application
rm -rf $targetPath
mkdir $targetPath
cp -rf * $targetPath
echo "Done copying"
