<?php
    $itemName = 'swin-casino';
    error_reporting(E_ALL);
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    function appUrl() {
    $current = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $exp     = explode('?action', $current);
    $url     = str_replace('index.php', '', $exp[0]);
    $url     = substr($url, 0, -8);
    return $url;
    }
    if ($action == 'requirements') {
    $passed      = [];
    $failed      = [];
    $requiredPHP = '8.2';
    $currentPHP  = explode('.', PHP_VERSION)[0] . '.' . explode('.', PHP_VERSION)[1];
    if (version_compare($currentPHP, $requiredPHP, '>=')) {
        $passed[] = "PHP version $requiredPHP+ is required";
    } else {
        $failed[] = "PHP version $requiredPHP+ is required. Your current PHP version is $currentPHP";
    }
    $extensions = ['BCMath', 'Ctype', 'cURL', 'DOM', 'Fileinfo', 'GD', 'JSON', 'Mbstring', 'OpenSSL', 'PCRE', 'PDO', 'pdo_mysql', 'Tokenizer', 'XML', 'Filter', 'Hash', 'Session', 'zip'];
    foreach ($extensions as $extension) {
        if (extension_loaded($extension)) {
            $passed[] = strtoupper($extension) . ' PHP Extension is required';
        } else {
            $failed[] = strtoupper($extension) . ' PHP Extension is required';
        }
    }
    if (function_exists('curl_version')) {
        $passed[] = 'Curl via PHP is required';
    } else {
        $failed[] = 'Curl via PHP is required';
    }
    if (file_get_contents(__FILE__)) {
        $passed[] = 'file_get_contents() is required';
    } else {
        $failed[] = 'file_get_contents() is required';
    }
    if (ini_get('allow_url_fopen')) {
        $passed[] = 'allow_url_fopen() is required';
    } else {
        $failed[] = 'allow_url_fopen() is required';
    }
    $dirs = ['../core/bootstrap/cache/', '../core/storage/', '../core/storage/app/', '../core/storage/framework/', '../core/storage/logs/'];
    foreach ($dirs as $dir) {
        $perm = substr(sprintf('%o', fileperms($dir)), -4);
        if ($perm >= '0775') {
            $passed[] = str_replace("../", "", $dir) . ' is required 0775 permission';
        } else {
            $failed[] = str_replace("../", "", $dir) . ' is required 0775 permission. Current Permisiion is ' . $perm;
        }
    }
    if (file_exists('database.sql')) {
        $passed[] = 'database.sql should be available';
    } else {
        $failed[] = 'database.sql should be available';
    }
    if (file_exists('../.htaccess')) {
        $passed[] = '".htaccess" should be available in root directory';
    } else {
        $failed[] = '".htaccess" should be available in root directory';
    }
    }

    if ($action == 'result') {
    $response = ['error' => 'ok', 'message' => ''];

    try {
        $db     = new PDO("mysql:host=$_POST[db_host];dbname={$_POST['db_name']}", $_POST['db_user'], $_POST['db_pass']);
        $dbinfo = $db->query('SELECT VERSION()')->fetchColumn();

        if (strpos(strtolower($dbinfo), 'mariadb') !== false) {
            $engine  = 'mariadb';
            $version = implode('.', array_slice(explode('.', $dbinfo), 0, 2));
        } else {
            $engine  = 'mysql';
            $version = implode('.', array_slice(explode('.', $dbinfo), 0, 2));
        }

        if (strtolower($engine) == 'mariadb') {
            if (!version_compare($version, '10.6', '>=')) {
                $response['error']   = 'error';
                $response['message'] = 'MariaDB 10.6+ Or MySQL 8.0+ Required. <br> Your current version is MariaDB ' . $version;
            }
        } else {
            if (!version_compare($version, '8.0', '>=')) {
                $response['error']   = 'error';
                $response['message'] = 'MariaDB 10.6+ Or MySQL 8.0+ Required. <br> Your current version is MySQL ' . $version;
            }
        }
    } catch (Exception $e) {
        $response['error']   = 'error';
        $response['message'] = 'Database Credential is Not Valid';
    }

    if (isset($response['error']) && $response['error'] == 'ok') {
        try {
            $query = file_get_contents("database.sql");
            $stmt  = $db->prepare($query);
            $stmt->execute();
            $stmt->closeCursor();
        } catch (Exception $e) {
            $response['error']   = 'error';
            $response['message'] = 'Problem Occurred When Importing Database!<br>Please Make Sure The Database is Empty.';
        }
    }

    if (isset($response['error']) && $response['error'] == 'ok') {
        try {
            $envLocation = '../core/.env';
            $appKey      = 'base64:' . base64_encode(random_bytes(32));
            $appName     = isset($_POST['app_name'])    ? trim($_POST['app_name'])    : 'SWin Casino';
            $brandName   = isset($_POST['brand_name'])  ? trim($_POST['brand_name'])  : 'Scoware';
            $brandUrl    = isset($_POST['brand_url'])   ? trim($_POST['brand_url'])   : 'https://scoware.com';
            $brandEmail  = isset($_POST['brand_email']) ? trim($_POST['brand_email']) : 'support@scoware.com';
            $envBody  = "APP_NAME=\"" . $appName . "\"\n";
            $envBody .= "APP_ENV=production\n";
            $envBody .= "APP_KEY=" . $appKey . "\n";
            $envBody .= "APP_DEBUG=false\n";
            $envBody .= "APP_TIMEZONE=UTC\n";
            $envBody .= "APP_URL=" . rtrim($_POST['url'], '/') . "\n\n";
            $envBody .= "APP_BRAND_NAME=\"" . $brandName . "\"\n";
            $envBody .= "APP_BRAND_URL=\"" . $brandUrl . "\"\n";
            $envBody .= "APP_BRAND_EMAIL=\"" . $brandEmail . "\"\n\n";
            $envBody .= "APP_LOCALE=en\n";
            $envBody .= "APP_FALLBACK_LOCALE=en\n";
            $envBody .= "APP_FAKER_LOCALE=en_US\n\n";
            $envBody .= "APP_MAINTENANCE_DRIVER=file\n\n";
            $envBody .= "BCRYPT_ROUNDS=12\n\n";
            $envBody .= "LOG_CHANNEL=stack\n";
            $envBody .= "LOG_STACK=single\n";
            $envBody .= "LOG_LEVEL=error\n\n";
            $envBody .= "DB_CONNECTION=mysql\n";
            $envBody .= "DB_HOST=" . $_POST['db_host'] . "\n";
            $envBody .= "DB_PORT=3306\n";
            $envBody .= "DB_DATABASE=" . $_POST['db_name'] . "\n";
            $envBody .= "DB_USERNAME=" . $_POST['db_user'] . "\n";
            $envBody .= "DB_PASSWORD=" . $_POST['db_pass'] . "\n\n";
            $envBody .= "SESSION_DRIVER=database\n";
            $envBody .= "SESSION_LIFETIME=120\n\n";
            $envBody .= "FILESYSTEM_DISK=local\n";
            $envBody .= "QUEUE_CONNECTION=database\n";
            $envBody .= "CACHE_STORE=database\n\n";
            $envBody .= "MAIL_MAILER=log\n";
            $envBody .= "MAIL_HOST=127.0.0.1\n";
            $envBody .= "MAIL_PORT=2525\n";
            $envBody .= "MAIL_USERNAME=null\n";
            $envBody .= "MAIL_PASSWORD=null\n";
            $envBody .= "MAIL_ENCRYPTION=null\n";
            $envBody .= "MAIL_FROM_ADDRESS=\"" . $brandEmail . "\"\n";
            $envBody .= "MAIL_FROM_NAME=\"" . $appName . "\"\n";

            file_put_contents($envLocation, $envBody);
        } catch (Exception $e) {
            $response['error']   = 'error';
            $response['message'] = 'Problem Occurred When Writing Environment File.';
        }
    }

    if (isset($response['error']) && $response['error'] == 'ok') {
        try {
            $db->query("UPDATE admins SET email='" . $_POST['email'] . "', username='" . $_POST['admin_user'] . "', password='" . password_hash($_POST['admin_pass'], PASSWORD_DEFAULT) . "' WHERE username='admin'");
            // Update site_name in general_settings from the installer form
            $siteName = isset($_POST['app_name']) ? $db->quote(trim($_POST['app_name'])) : "'SWin Casino'";
            $db->query("UPDATE general_settings SET site_name=" . $siteName . " WHERE id=1");
        } catch (Exception $e) {
            $response['message'] = 'Installer was unable to set the admin credentials.';
        }
    }

    if (isset($response['error']) && $response['error'] == 'ok') {
        // ── 1. Create the storage symlink ──────────────────────────────────────
        // The web root is the parent of the install/ directory (i.e. parent of __DIR__).
        // We need <web-root>/storage  →  <web-root>/core/storage/app/public
        $rootDir    = dirname(__DIR__);
        $linkPath   = $rootDir . '/storage';
        $targetPath = $rootDir . '/core/storage/app/public';

        if (!is_dir($targetPath)) {
            @mkdir($targetPath, 0775, true);
        }
        if (!file_exists($linkPath) && !is_link($linkPath)) {
            @symlink($targetPath, $linkPath);
        }

        // ── 2. Warm Laravel caches (best-effort; exec may be disabled on some hosts) ──
        if (function_exists('exec')) {
            $phpBin  = PHP_BINARY;
            $artisan = $rootDir . '/core/artisan';
            if (file_exists($artisan)) {
                @exec(escapeshellcmd($phpBin) . ' ' . escapeshellarg($artisan) . ' optimize:clear 2>/dev/null');
                @exec(escapeshellcmd($phpBin) . ' ' . escapeshellarg($artisan) . ' config:cache   2>/dev/null');
                @exec(escapeshellcmd($phpBin) . ' ' . escapeshellarg($artisan) . ' route:cache    2>/dev/null');
                @exec(escapeshellcmd($phpBin) . ' ' . escapeshellarg($artisan) . ' view:cache     2>/dev/null');
            }
        }

        // ── 3. Self-delete the install/ directory after the response is sent ──
        $installDir = __DIR__;
        register_shutdown_function(function () use ($installDir) {
            $files = array_merge(
                glob($installDir . '/*')  ?: [],
                glob($installDir . '/.*') ?: []
            );
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            @rmdir($installDir);
        });
    }
    }
    $sectionTitle = empty($action) ? 'Terms of Use' : $action;
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Easy Installer</title>
	<link rel="stylesheet" href="../assets/global/css/bootstrap.min.css">
	<link rel="stylesheet" href="../assets/global/css/all.min.css">
	<link rel="stylesheet" href="../assets/global/css/installer.css">
</head>

<body>
	<header class="py-3 border-bottom border-primary bg--dark">
		<div class="container">
			<div class="d-flex align-items-center justify-content-between header gap-3">
				<h3 class="title"><?php echo isset($_POST['app_name']) ? htmlspecialchars($_POST['app_name']) : 'SWin Casino'; ?></h3>
				<h3 class="title">Easy Installer</h3>
			</div>
		</div>
	</header>
	<div class="installation-section padding-bottom padding-top">
		<div class="container">
			<div class="installation-wrapper">
				<div class="install-content-area">
					<div class="install-item">
						<h3 class="title text-center"><?php echo $sectionTitle; ?></h3>
						<div class="box-item">
							<?php
                                if ($action == 'result') {
                                    echo '<div class="success-area text-center">';
                                    if (isset($response['error']) && $response['error'] == 'ok') {
                                        echo '<h2 class="text-success text-uppercase mb-3">Your system has been installed successfully!</h2>';
                                        if (isset($response['message']) && $response['message']) {
                                            echo '<h5 class="text-warning mb-3">' . $response['message'] . '</h5>';
                                        }
                                        echo '<p class="text-success lead my-3"><i class="fas fa-check-circle"></i> The installer folder has been removed automatically.</p>';
                                        echo '<div class="warning"><a href="' . appUrl() . '" class="theme-button choto">Go to website</a></div>';
                                    } else {
                                        if (isset($response['message']) && $response['message']) {
                                            echo '<h3 class="text-danger mb-3">' . $response['message'] . '</h3>';
                                        } else {
                                            echo '<h3 class="text-danger mb-3">Your Server is not Capable to Handle the Request.</h3>';
                                        }
                                        echo '<div class="warning mt-2"><h5 class="mb-4 fw-normal">Try again. Please check your database credentials and server requirements.</h5><a href="?action=information" class="theme-button choto me-1 mb-3">Try Again</a></div>';

                                    }
                                    echo '</div>';
                                } else if ($action == 'information') {
                                ?>
								<form action="?action=result" method="post" class="information-form-area mb--20">
									<div class="info-item">
										<h5 class="font-weight-normal mb-2">Application Branding</h5>
										<div class="row">
											<div class="information-form-group col-sm-6">
												<label>Site / Casino Name</label>
												<input type="text" name="app_name" placeholder="SWin Casino" value="SWin Casino" required>
											</div>
											<div class="information-form-group col-sm-6">
												<label>Developer / Brand Name</label>
												<input type="text" name="brand_name" placeholder="Scoware" value="Scoware">
											</div>
											<div class="information-form-group col-sm-6">
												<label>Brand Website URL</label>
												<input type="url" name="brand_url" placeholder="https://scoware.com" value="https://scoware.com">
											</div>
											<div class="information-form-group col-sm-6">
												<label>Brand / Support Email</label>
												<input type="email" name="brand_email" placeholder="support@scoware.com" value="support@scoware.com">
											</div>
										</div>
									</div>
									<div class="info-item">
										<h5 class="font-weight-normal mb-2">Website URL</h5>
										<div class="row">
											<div class="information-form-group col-12">
												<input name="url" value="<?php echo appUrl(); ?>" type="text" required>
											</div>
										</div>
									</div>
									<div class="info-item">
										<h5 class="font-weight-normal mb-2">Database Details</h5>
										<div class="row">
											<div class="information-form-group col-sm-6">
												<input type="text" name="db_name" placeholder="Database Name" required>
											</div>
											<div class="information-form-group col-sm-6">
												<input type="text" name="db_host" placeholder="Database Host" required>
											</div>
											<div class="information-form-group col-sm-6">
												<input type="text" name="db_user" placeholder="Database User" required>
											</div>
											<div class="information-form-group col-sm-6">
												<input class="secure-password" type="text" name="db_pass" placeholder="Database Password">
											</div>
										</div>
									</div>
									<div class="info-item">
										<h5 class="font-weight-normal mb-3">Admin Credential</h5>
										<div class="row">
											<div class="information-form-group col-lg-3 col-sm-6">
												<label>Username</label>
												<input name="admin_user" type="text" placeholder="Admin Username" required>
											</div>
											<div class="information-form-group col-lg-3 col-sm-6">
												<label>Password</label>
												<input name="admin_pass" type="text" placeholder="Admin Password" required>
											</div>
											<div class="information-form-group col-lg-6">
												<label>Email Address</label>
												<input name="email" placeholder="Your Email address" type="email" required>
											</div>
										</div>
									</div>
									<div class="info-item">
										<div class="information-form-group text-end">
											<button type="submit" class="theme-button choto">Install Now</button>
										</div>
									</div>
								</form>
							<?php
                                } else if ($action == 'requirements') {
                                    $btnText = 'View Detailed Check Result';
                                    if (count($failed)) {
                                        $btnText = 'View Passed Check';
                                        echo '<div class="item table-area"><table class="requirment-table">';
                                        foreach ($failed as $fail) {
                                            echo "<tr><td>$fail</td><td><i class='fas fa-times'></i></td></tr>";
                                        }
                                        echo '</table></div>';
                                    }
                                    if (!count($failed)) {
                                        echo '<div class="text-center"><i class="far fa-check-circle success-icon text-success"></i><h5 class="my-3">Requirements Check Passed!</h5></div>';
                                    }
                                    if (count($passed)) {
                                        echo '<div class="text-center my-3"><button class="btn passed-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePassed" aria-expanded="false" aria-controls="collapsePassed">' . $btnText . '</button></div>';
                                        echo '<div class="collapse mb-4" id="collapsePassed"><div class="item table-area"><table class="requirment-table">';
                                        foreach ($passed as $pass) {
                                            echo "<tr><td>$pass</td><td><i class='fas fa-check'></i></td></tr>";
                                        }
                                        echo '</table></div></div>';
                                    }
                                    echo '<div class="item text-end mt-3">';
                                    if (count($failed)) {
                                        echo '<a class="theme-button btn-warning choto" href="?action=requirements">ReCheck <i class="fas fa-sync-alt"></i></a>';
                                    } else {
                                        echo '<a class="theme-button choto" href="?action=information">Next Step <i class="fas fa-angle-double-right"></i></a>';
                                    }
                                    echo '</div>';
                                } else {
                                ?>
								<div class="item">
									<h4 class="subtitle">License to be used on one(1) domain(website) only!</h4>
									<p> The Regular license is for one website or domain only. If you want to use it on multiple websites or domains you have to purchase more licenses (1 website = 1 license). The Regular License grants you an ongoing, non-exclusive, worldwide license to make use of the item.</p>
								</div>
								<div class="item">
									<h5 class="subtitle font-weight-bold">You Can:</h5>
									<ul class="check-list">
										<li> Use on one(1) domain only. </li>
										<li> Modify or edit as you want. </li>
										<li> Translate to your choice of language(s).</li>
									</ul>
									<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> If any issue or error occurred for your modification on our code/database, we will not be responsible for that. </span>
								</div>
								<div class="item">
									<h5 class="subtitle font-weight-bold">You Cannot: </h5>
									<ul class="check-list">
										<li class="no"> Resell, distribute, give away, or trade by any means to any third party or individual. </li>
										<li class="no"> Include this product into other products sold on any market or affiliate websites. </li>
										<li class="no"> Use on more than one(1) domain. </li>
									</ul>
								</div>
								<div class="item">
									<p class="info">Proceed to the next step to install the application.</p>
								</div>
								<div class="item text-end">
									<a href="?action=requirements" class="theme-button choto">I Agree, Next Step</a>
								</div>
							<?php
                                }
                            ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<footer class="py-3 text-center bg--dark border-top border-primary">
		<div class="container">
			<p class="m-0 font-weight-bold">&copy;<?php echo Date('Y') ?> - Scoware. All Rights Reserved.</p>
		</div>
	</footer>
	<script src="../assets/global/js/bootstrap.bundle.min.js"></script>
</body>
</html>
