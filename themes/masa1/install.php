
<style type="text/css">
	label{
		font-weight: bold;
	}

	code{
		display: block;
		padding: 3px 5px;
		border: 1px solid #000;
		background-color: #ccc;
		border-radius: 4px;
		margin-top: 5px;
		margin-bottom: 5px;
	}

	input{
		width: 100%;
		padding: 5px;
		border: 1px solid #000;
		border-radius: 4px;
	}

	#body-container{
		max-width: 600px;
		margin: 0 auto;
	}

	.row{
		margin-bottom: 15px;
	}

    .files-list {
        list-style: none;
        margin-top: 8px;
        margin-bottom: 8px;
    }

	.submit{
		padding: 10px;
		max-width: 150px;
		float: right;
	}
</style>

<div id="body-container">

	<form action="install.php" method="POST">

		<div><h1>1 Step Install</h1></div>

        <div class="row">
            <div><label for="database-address">Important:</label></div>
            <div>
                Before start, give permission to www-data user to the following dictories:
                <ul class="files-list">
                    <li>- /var/www (home folder of www-data)</li>
                </ul>
                You can do it with the following command:
                <code>
                    sudo chown -R www-data /var/www
                </code>
            </div>
        </div>

		<div class="row">
			<div><label for="database-address">Data Address:</label></div>
			<div><input type="text" id="database-address" name="database-address" value="<?php echo $base_dir; ?>/data" /></div>
			<div>Obs.: must be a place where www-data user has access.</div>
		</div>

		<div class="row">
			<div><label for="domain">Database Domain:</label></div>
			<div><input type="text" id="domain" name="domain" value="localhost" /></div>
		</div>

		<div class="row">
			<div><label for="displayErrorDetails">Display Errors:</label></div>
			<div><input type="text" id="displayErrorDetails" name="displayErrorDetails" value="true"/></div>
		</div>

		<div class="row">
			<div>
				<label for="private_key">Private Key Address:</label>
			</div>
			<div>
				<input type="text" name="private_key" id="private_key" value="<?php echo $base_dir ?>/certs/masadb.key" />
			</div>
			<div>
				Obs.: can be generated by: 
				<!-- <code>ssh-keygen -t rsa -b 4096</code> -->
				<code>sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout masadb.key -out masadb.crt</code>
				<!-- <code>openssl req -new -newkey rsa:2048 -nodes -keyout masadb.key -out masadb.csr</code> -->
				<code>openssl rsa -in masadb.key -pubout > masadb.pub</code>
			</div>
			<div>
				Obs.: in case of nginx, it may require to comment the line <i>include snippets/ssl-params.conf;</i> for local installations.
			</div>
		</div>

		<div class="row">
			<div><label for="public_key">Public Key Address:</label></div>
			<div><input type="text" name="public_key" id="public_key" value="<?php echo $base_dir ?>/certs/masadb.pub"/></div>
		</div>

		<div class="row">
			<div>&nbsp;</div>
			<div><input type="submit" value="Next" class="submit" /></div>
		</div>

	</form>

</div>