<?php
    session_start();
    include ("../config.php");
	include("../lib.php");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">

	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../images/favicon.ico">

	<?php
        $userID=$_SESSION["currentUserID"];
		
		if(isset($_GET["error"]))
		{
			if ($_GET["error"] == "currentpwnomatch")
			{
				$currentPwError = "Current password does not match.";
			}
		}
		
		if (!isset($_SESSION["currentUserID"]))
        {
            redirect("../login/");
        }
		
		if(isset($_POST["submit"]))
		{
			$fullname = $_POST["fullname"];
			$email = $_POST["email"];
			$bio = $_POST["bio"];
			$country = $_POST["country"];
			$dob = $_POST["dob"];
			$gravitarEmail = $_POST["gravitarEmail"];
			
			if ($gravitarEmail != "")
			{
				$gravitarhash = md5( strtolower( trim( $gravitarEmail ) ) );
				
				$profileimage = "https://www.gravatar.com/avatar/" . $gravitarhash . "?s=400";
				
				$dbQuery=$db->prepare("update users set fullname=:fullname, email=:email, bio=:bio, country=:country, dob=:dob, profileimage=:profileimage where id=:id");
				$dbParams=array('id'=>$userID,'fullname'=>$fullname,'email'=>$email,'bio'=>$bio,'country'=>$country,'dob'=>$dob,'profileimage'=>$profileimage);
				$dbQuery->execute($dbParams);
			}
			else
			{
				$dbQuery=$db->prepare("update users set fullname=:fullname, email=:email, bio=:bio, country=:country, dob=:dob where id=:id");
				$dbParams=array('id'=>$userID,'fullname'=>$fullname,'email'=>$email,'bio'=>$bio,'country'=>$country,'dob'=>$dob);
				$dbQuery->execute($dbParams);
			}
			
			echo "<script>window.location.href = 'edit.php?success=1'</script>";
		}
		else if(isset($_POST["updatepassword"]))
		{
			$current = $_POST["current"];
			$password = $_POST["password"];
			$userid = $_POST["userid"];
			
			$current = md5($current);
			
			$dbQuery=$db->prepare("select * from users where password=:current");
			$dbParams=array('current'=>$current);
			$dbQuery->execute($dbParams);
			$currentPwMatch = $dbQuery->rowCount();
			
			if ($currentPwMatch < 1)
			{
				redirect("edit.php?action=changepassword&error=currentpwnomatch");
			}
			else
			{
				$password = md5($password);
				
				$dbQuery=$db->prepare("update users set password=:password where id=:userid");
				$dbParams=array('userid'=>$userid,'password'=>$password);
				$dbQuery->execute($dbParams);
				redirect("edit.php?success=2");
			}
		}
		
		$dbQuery=$db->prepare("select * from users where id=:id");
        $dbParams = array('id'=>$userID);
        $dbQuery->execute($dbParams);
        //$dbRow=$dbQuery->fetch(PDO::FETCH_ASSOC);

        while ($dbRow = $dbQuery->fetch(PDO::FETCH_ASSOC))
        {
			$profileimage=$dbRow["profileimage"];
			$username=$dbRow["username"];
			$fullname=$dbRow["fullname"];
			$password=$dbRow["password"];
			$email=$dbRow["email"];
			$bio=$dbRow["bio"];
			$country=$dbRow["country"];
			$dob=$dbRow["dob"];
			$timestamp=$dbRow["lastlogin"];
        }
		
		$lastlogin = gmdate("Y-m-d H:i:s", $timestamp);
	?>
	
    <title><?php echo $username;?> | Profile</title>
	
	<!--DK CSS-->
	<link href="../styles.css" rel="stylesheet">
	
	
	<script>
		function validateForm() {
			var pwNew = document.forms["pwChange"]["current"].value;
			var pw = document.forms["pwChange"]["password"].value;
			var pwConfirm = document.forms["pwChange"]["passwordConfirm"].value;
			
			if (pw != pwConfirm) {
				alert("Passwords must match!");
				return false;
			}
		}
	</script>
	
	</head>

	<body>

		<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #1E88FF;">
		<!--<nav class="navbar navbar-expand-lg navbar-light bg-light">-->
		  <a class="navbar-brand" href="../index.php"><?php echo $sitename;?></a>
		  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		  </button>
		  <div class="collapse navbar-collapse" id="navbarText">
			<ul class="navbar-nav mr-auto">
			  <li class="nav-item">
				<a class="nav-link" href="../">Home</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link" href="../course/">Courses</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link" href="../dashboard/">Dashboard</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link" href="../contact/">Contact</a>
			  </li>
			  <li class="nav-item active">
				<a class="nav-link" href="../profile/">Profile</a>
			  </li>
			  <li class="nav-item">
				<?php if (has_capability("site:config",$userID)) { echo '<a class="nav-link" href="../settings/">Administration</a>'; } ?>
			  </li>
			</ul>
			<span class="navbar-text">
			
			  <?php
				if (isset($username)) {
					echo "<img src='".$profileimage."' width='28px' alt='Profile Image' class='rounded-circle'>&nbsp;<a href='../profile/'>".$fullname." (<a href='../profile/killSession.php'>Log out</a>)</a>";
				}
				else {
					echo "<a href='../login/'>Log in or sign up</a>";
				}
			  ?>
			</span>
		  </div>
		</nav>

		<div class="container">

			<br>
			
			<?php
				if(isset($_GET["success"]) && $_GET["success"] == 1)
				{				
					echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
							<strong>Success!</strong> Profile updated!
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
							</button>
						</div>';
				}
				else if(isset($_GET["success"]) && $_GET["success"] == 2)
				{				
					echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
							<strong>Success!</strong> Password updated!
							<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
							</button>
						</div>';
				}

				echo "<h5 style='float:left; vertical-align:middle;'>Logged in as ". $username ."</h5>";
			?>

			<button type="button" class="btn btn-primary btn-sm" style="float:right" onclick="window.location.href='killSession.php'">Log off</button>

			<br><br>
			
			<ul class="nav nav-tabs">
			  <li class="nav-item">
				<a class="nav-link" href="../profile">Profile</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link active" href="edit.php">Edit</a>
			  </li>
			</ul>

			<br>
			
			<?php
				if (isset($_GET["action"]) && $_GET["action"] == "changepassword")
				{
			?>
					<h3>Change Password</h3>
					<form method="post" name="pwChange" onsubmit="return validateForm()" action="edit.php">
						<div class="form-group">
							<label for="current">Current password<span class="required">&nbsp;&nbsp;&nbsp;<?php echo $currentPwError ?></span></label>
							<input class="form-control" id="current" name="current" type="password" aria-describedby="currentHelp" placeholder="Current password" required>
							<small id="currentHelp" class="form-text text-muted"><span class="required">* Required&nbsp;&nbsp;&nbsp;</span>Enter your current account password.</small>
						</div>
						
						<div class="form-group">
							<label for="password">New Password<span class="required"></span></label>
							<input class="form-control" id="password" name="password" type="password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{7,50}" aria-describedby="passwordHelp" placeholder="New password">
							<small id="usernameHelp" class="form-text text-muted"><span class="required">* Required&nbsp;&nbsp;&nbsp;</span>Please enter a new password. Must be between 7-50 characters, with at least one character lowercase, uppercase and number.</small>
						</div>
						
						<div class="form-group">
							<label for="passwordConfirm">Username<span class="required"></span></label>
							<input class="form-control" id="passwordConfirm" name="passwordConfirm" type="password" aria-describedby="passwordConfirmHelp" placeholder="Confirm password" required>
							<small id="passwordConfirmHelp" class="form-text text-muted"><span class="required">* Required&nbsp;&nbsp;&nbsp;</span>Must match the new password, as entered above.</small>
						</div>
						
						<input type="hidden" name="userid" value="<?php echo $userID; ?>">
						<button type="submit" name="updatepassword" class="btn btn-primary">Update password</button>
					</form>
			
			<?php		
				}
				else
				{
			?>
			<h3>Edit Profile</h3>
			<form method="post" action="edit.php">
				<div class="form-group">
					<label for="username">Username</label>
					<input class="form-control" id="username" name="username" type="text" aria-describedby="usernameHelp" placeholder="<?php echo $username; ?>" readonly>
					<small id="usernameHelp" class="form-text text-muted">Only administrators can alter an account's username once created. Click <a href="../message?role=admin">here</a> to message one.</small>
				</div>
				
				<div class="form-group">
					<label for="fullname">Full name</label>
					<input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo $fullname; ?>">
				</div>
				
				<div class="form-group">
					<label for="email">Email address</label>
					<input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>">
				</div>
			
				<div class="form-row">
					<div class="form-group col-md-12">
						<label for="bio">Your bio</label>
						<textarea class="form-control" id="bio" name="bio" rows="5"><?php echo $bio; ?></textarea>
					</div>
				</div>
				
				<div class="form-row">
					<div class="form-group col-md-12">
						<label for="country">Country</label>
						<select id="country" name="country" class="form-control">
							<option value="<?php echo $country; ?>" selected><?php echo $country; ?></option> 
							<option value="United States">United States</option> 
							<option value="United Kingdom">United Kingdom</option> 
							<option value="Afghanistan">Afghanistan</option> 
							<option value="Albania">Albania</option> 
							<option value="Algeria">Algeria</option> 
							<option value="American Samoa">American Samoa</option> 
							<option value="Andorra">Andorra</option> 
							<option value="Angola">Angola</option> 
							<option value="Anguilla">Anguilla</option> 
							<option value="Antarctica">Antarctica</option> 
							<option value="Antigua and Barbuda">Antigua and Barbuda</option> 
							<option value="Argentina">Argentina</option> 
							<option value="Armenia">Armenia</option> 
							<option value="Aruba">Aruba</option> 
							<option value="Australia">Australia</option> 
							<option value="Austria">Austria</option> 
							<option value="Azerbaijan">Azerbaijan</option> 
							<option value="Bahamas">Bahamas</option> 
							<option value="Bahrain">Bahrain</option> 
							<option value="Bangladesh">Bangladesh</option> 
							<option value="Barbados">Barbados</option> 
							<option value="Belarus">Belarus</option> 
							<option value="Belgium">Belgium</option> 
							<option value="Belize">Belize</option> 
							<option value="Benin">Benin</option> 
							<option value="Bermuda">Bermuda</option> 
							<option value="Bhutan">Bhutan</option> 
							<option value="Bolivia">Bolivia</option> 
							<option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option> 
							<option value="Botswana">Botswana</option> 
							<option value="Bouvet Island">Bouvet Island</option> 
							<option value="Brazil">Brazil</option> 
							<option value="British Indian Ocean Territory">British Indian Ocean Territory</option> 
							<option value="Brunei Darussalam">Brunei Darussalam</option> 
							<option value="Bulgaria">Bulgaria</option> 
							<option value="Burkina Faso">Burkina Faso</option> 
							<option value="Burundi">Burundi</option> 
							<option value="Cambodia">Cambodia</option> 
							<option value="Cameroon">Cameroon</option> 
							<option value="Canada">Canada</option> 
							<option value="Cape Verde">Cape Verde</option> 
							<option value="Cayman Islands">Cayman Islands</option> 
							<option value="Central African Republic">Central African Republic</option> 
							<option value="Chad">Chad</option> 
							<option value="Chile">Chile</option> 
							<option value="China">China</option> 
							<option value="Christmas Island">Christmas Island</option> 
							<option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option> 
							<option value="Colombia">Colombia</option> 
							<option value="Comoros">Comoros</option> 
							<option value="Congo">Congo</option> 
							<option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option> 
							<option value="Cook Islands">Cook Islands</option> 
							<option value="Costa Rica">Costa Rica</option> 
							<option value="Cote D'ivoire">Cote D'ivoire</option> 
							<option value="Croatia">Croatia</option> 
							<option value="Cuba">Cuba</option> 
							<option value="Cyprus">Cyprus</option> 
							<option value="Czech Republic">Czech Republic</option> 
							<option value="Denmark">Denmark</option> 
							<option value="Djibouti">Djibouti</option> 
							<option value="Dominica">Dominica</option> 
							<option value="Dominican Republic">Dominican Republic</option> 
							<option value="Ecuador">Ecuador</option> 
							<option value="Egypt">Egypt</option> 
							<option value="El Salvador">El Salvador</option> 
							<option value="Equatorial Guinea">Equatorial Guinea</option> 
							<option value="Eritrea">Eritrea</option> 
							<option value="Estonia">Estonia</option> 
							<option value="Ethiopia">Ethiopia</option> 
							<option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option> 
							<option value="Faroe Islands">Faroe Islands</option> 
							<option value="Fiji">Fiji</option> 
							<option value="Finland">Finland</option> 
							<option value="France">France</option> 
							<option value="French Guiana">French Guiana</option> 
							<option value="French Polynesia">French Polynesia</option> 
							<option value="French Southern Territories">French Southern Territories</option> 
							<option value="Gabon">Gabon</option> 
							<option value="Gambia">Gambia</option> 
							<option value="Georgia">Georgia</option> 
							<option value="Germany">Germany</option> 
							<option value="Ghana">Ghana</option> 
							<option value="Gibraltar">Gibraltar</option> 
							<option value="Greece">Greece</option> 
							<option value="Greenland">Greenland</option> 
							<option value="Grenada">Grenada</option> 
							<option value="Guadeloupe">Guadeloupe</option> 
							<option value="Guam">Guam</option> 
							<option value="Guatemala">Guatemala</option> 
							<option value="Guinea">Guinea</option> 
							<option value="Guinea-bissau">Guinea-bissau</option> 
							<option value="Guyana">Guyana</option> 
							<option value="Haiti">Haiti</option> 
							<option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option> 
							<option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option> 
							<option value="Honduras">Honduras</option> 
							<option value="Hong Kong">Hong Kong</option> 
							<option value="Hungary">Hungary</option> 
							<option value="Iceland">Iceland</option> 
							<option value="India">India</option> 
							<option value="Indonesia">Indonesia</option> 
							<option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option> 
							<option value="Iraq">Iraq</option> 
							<option value="Ireland">Ireland</option> 
							<option value="Israel">Israel</option> 
							<option value="Italy">Italy</option> 
							<option value="Jamaica">Jamaica</option> 
							<option value="Japan">Japan</option> 
							<option value="Jordan">Jordan</option> 
							<option value="Kazakhstan">Kazakhstan</option> 
							<option value="Kenya">Kenya</option> 
							<option value="Kiribati">Kiribati</option> 
							<option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option> 
							<option value="Korea, Republic of">Korea, Republic of</option> 
							<option value="Kuwait">Kuwait</option> 
							<option value="Kyrgyzstan">Kyrgyzstan</option> 
							<option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option> 
							<option value="Latvia">Latvia</option> 
							<option value="Lebanon">Lebanon</option> 
							<option value="Lesotho">Lesotho</option> 
							<option value="Liberia">Liberia</option> 
							<option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option> 
							<option value="Liechtenstein">Liechtenstein</option> 
							<option value="Lithuania">Lithuania</option> 
							<option value="Luxembourg">Luxembourg</option> 
							<option value="Macao">Macao</option> 
							<option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option> 
							<option value="Madagascar">Madagascar</option> 
							<option value="Malawi">Malawi</option> 
							<option value="Malaysia">Malaysia</option> 
							<option value="Maldives">Maldives</option> 
							<option value="Mali">Mali</option> 
							<option value="Malta">Malta</option> 
							<option value="Marshall Islands">Marshall Islands</option> 
							<option value="Martinique">Martinique</option> 
							<option value="Mauritania">Mauritania</option> 
							<option value="Mauritius">Mauritius</option> 
							<option value="Mayotte">Mayotte</option> 
							<option value="Mexico">Mexico</option> 
							<option value="Micronesia, Federated States of">Micronesia, Federated States of</option> 
							<option value="Moldova, Republic of">Moldova, Republic of</option> 
							<option value="Monaco">Monaco</option> 
							<option value="Mongolia">Mongolia</option> 
							<option value="Montserrat">Montserrat</option> 
							<option value="Morocco">Morocco</option> 
							<option value="Mozambique">Mozambique</option> 
							<option value="Myanmar">Myanmar</option> 
							<option value="Namibia">Namibia</option> 
							<option value="Nauru">Nauru</option> 
							<option value="Nepal">Nepal</option> 
							<option value="Netherlands">Netherlands</option> 
							<option value="Netherlands Antilles">Netherlands Antilles</option> 
							<option value="New Caledonia">New Caledonia</option> 
							<option value="New Zealand">New Zealand</option> 
							<option value="Nicaragua">Nicaragua</option> 
							<option value="Niger">Niger</option> 
							<option value="Nigeria">Nigeria</option> 
							<option value="Niue">Niue</option> 
							<option value="Norfolk Island">Norfolk Island</option> 
							<option value="Northern Mariana Islands">Northern Mariana Islands</option> 
							<option value="Norway">Norway</option> 
							<option value="Oman">Oman</option> 
							<option value="Pakistan">Pakistan</option> 
							<option value="Palau">Palau</option> 
							<option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option> 
							<option value="Panama">Panama</option> 
							<option value="Papua New Guinea">Papua New Guinea</option> 
							<option value="Paraguay">Paraguay</option> 
							<option value="Peru">Peru</option> 
							<option value="Philippines">Philippines</option> 
							<option value="Pitcairn">Pitcairn</option> 
							<option value="Poland">Poland</option> 
							<option value="Portugal">Portugal</option> 
							<option value="Puerto Rico">Puerto Rico</option> 
							<option value="Qatar">Qatar</option> 
							<option value="Reunion">Reunion</option> 
							<option value="Romania">Romania</option> 
							<option value="Russian Federation">Russian Federation</option> 
							<option value="Rwanda">Rwanda</option> 
							<option value="Saint Helena">Saint Helena</option> 
							<option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option> 
							<option value="Saint Lucia">Saint Lucia</option> 
							<option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option> 
							<option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option> 
							<option value="Samoa">Samoa</option> 
							<option value="San Marino">San Marino</option> 
							<option value="Sao Tome and Principe">Sao Tome and Principe</option> 
							<option value="Saudi Arabia">Saudi Arabia</option> 
							<option value="Senegal">Senegal</option> 
							<option value="Serbia and Montenegro">Serbia and Montenegro</option> 
							<option value="Seychelles">Seychelles</option> 
							<option value="Sierra Leone">Sierra Leone</option> 
							<option value="Singapore">Singapore</option> 
							<option value="Slovakia">Slovakia</option> 
							<option value="Slovenia">Slovenia</option> 
							<option value="Solomon Islands">Solomon Islands</option> 
							<option value="Somalia">Somalia</option> 
							<option value="South Africa">South Africa</option> 
							<option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option> 
							<option value="Spain">Spain</option> 
							<option value="Sri Lanka">Sri Lanka</option> 
							<option value="Sudan">Sudan</option> 
							<option value="Suriname">Suriname</option> 
							<option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option> 
							<option value="Swaziland">Swaziland</option> 
							<option value="Sweden">Sweden</option> 
							<option value="Switzerland">Switzerland</option> 
							<option value="Syrian Arab Republic">Syrian Arab Republic</option> 
							<option value="Taiwan, Province of China">Taiwan, Province of China</option> 
							<option value="Tajikistan">Tajikistan</option> 
							<option value="Tanzania, United Republic of">Tanzania, United Republic of</option> 
							<option value="Thailand">Thailand</option> 
							<option value="Timor-leste">Timor-leste</option> 
							<option value="Togo">Togo</option> 
							<option value="Tokelau">Tokelau</option> 
							<option value="Tonga">Tonga</option> 
							<option value="Trinidad and Tobago">Trinidad and Tobago</option> 
							<option value="Tunisia">Tunisia</option> 
							<option value="Turkey">Turkey</option> 
							<option value="Turkmenistan">Turkmenistan</option> 
							<option value="Turks and Caicos Islands">Turks and Caicos Islands</option> 
							<option value="Tuvalu">Tuvalu</option> 
							<option value="Uganda">Uganda</option> 
							<option value="Ukraine">Ukraine</option> 
							<option value="United Arab Emirates">United Arab Emirates</option> 
							<option value="United Kingdom">United Kingdom</option> 
							<option value="United States">United States</option> 
							<option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option> 
							<option value="Uruguay">Uruguay</option> 
							<option value="Uzbekistan">Uzbekistan</option> 
							<option value="Vanuatu">Vanuatu</option> 
							<option value="Venezuela">Venezuela</option> 
							<option value="Viet Nam">Viet Nam</option> 
							<option value="Virgin Islands, British">Virgin Islands, British</option> 
							<option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option> 
							<option value="Wallis and Futuna">Wallis and Futuna</option> 
							<option value="Western Sahara">Western Sahara</option> 
							<option value="Yemen">Yemen</option> 
							<option value="Zambia">Zambia</option> 
							<option value="Zimbabwe">Zimbabwe</option>
						</select>
					</div>
				</div>
				
				<div class="form-row">
					<div class="form-group col-md-12">
						<label for="dob">Date of birth</label>
						<input type="text" class="form-control" id="dob" name="dob" value="<?php echo $dob; ?>">
					</div>
				</div>
				
				<div class="form-group">
					<label for="gravitarEmail">Gravitar Email Address</label>
					<input type="email" class="form-control" id="gravitarEmail" name="gravitarEmail" aria-describedby="gravitarEmailHelp" placeholder="Enter Gravitar email">
					<small id="gravitarEmailHelp" class="form-text text-muted">If you do not have a Gravitar, you can create one <a href="https://en.gravatar.com/">here.</a></small>
				</div>
				
				<p>Change your password <a href="edit.php?action=changepassword">here</a></p>
				
				<button type="submit" name="submit" class="btn btn-primary">Update Profile</button>
			</form>
			
			<?php
				}
			?>
			<br>

        </div>

      <footer>
		<p class="copyright"><?php echo $sitename ." | &copy ". date("Y"); ?></p>
		<ul class="v-links">
			<li><a href="../">Home</a></li>
			<li><a href="../course">Courses</a></li>
			<li><a href="../dashboard">Dashboard</a></li>
			<li><a href="../contact">Contact</a></li>
			<li><a href="../profile">Profile</a></li>
		</ul>
	  </footer>
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js" integrity="sha384-a5N7Y/aK3qNeh15eJKGWxsqtnX/wWdSZSKp+81YjTmS15nvnvxKHuzaWwXHDli+4" crossorigin="anonymous"></script>
	</body>
</html>
