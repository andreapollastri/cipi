# USER PERMISSIONS FIX
sudo cp /bin/bash /bin/rbash
sudo sed -i -e '/cp/s/bin\/bash/bin\/rbash/' /etc/passwd