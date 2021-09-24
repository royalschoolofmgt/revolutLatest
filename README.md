# BigCommerce Revolut Payment Gateway App

This plugin allows you to integrate payment solution with BigCommerce platform. This app was built using codeigniter framework.

## App Installation

To get the app running locally, follow these instructions:

1. Create a folder to hold your installation: mkdir revolut
2. Copy the contents of the zip from git repo
3. Specify Database details in `.env` file
	- If using MySQL, enter your mysql database config keys (hostname, database, username/password) like below.
	database.default.hostname = localhost
	database.default.database = revolut
	database.default.username = root
	database.default.password = 
	database.default.DBDriver = MySQLi
	- If you want to change baseurl and project assets path change(app.baseURL , app.ASSETSPATH) in `.env` file.
4. Run the command from the project root directory
	- php spark migrate
5. Your set up is ready.

Code is validated using [official coding standards of CodeIgniter](https://github.com/CodeIgniter/coding-standard)

## BigCommerce Installation Instructions

To run this app you need to have bigcommerce account.

1. Login to [Bigcommerce](https://login.bigcommerce.com/)
2. Open [Bigcommerce Devtools](https://devtools.bigcommerce.com/my/apps)
3. Create your App and add required details.
4. Open [Bigcommerce Dashboard](https://login.bigcommerce.com/).
5. Check Apps section in left panel.
		- Apps
			- MyApps
				- My Draft Apps in center panel.
6. Install the app.

Licensed to [247Commerce](https://www.247commerce.co.uk/PRODUCT-APP-LICENSE-POLICY-v1.6-Sept-2021.pdf)

