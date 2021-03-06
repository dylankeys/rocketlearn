<?php
	session_start();
	include("../config.php");
	include("../lib.php");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
	<script defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js" integrity="sha384-SlE991lGASHoBfWbelyBPLsUlwY1GwNDJo3jSJO04KZ33K2bwfV9YBauFfnzvynJ" crossorigin="anonymous"></script>

	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../images/favicon.ico">

	<?php
        $userID=$_SESSION["currentUserID"];
		
		$dbQuery=$db->prepare("select * from users where id=:id");
        $dbParams = array('id'=>$userID);
        $dbQuery->execute($dbParams);
        //$dbRow=$dbQuery->fetch(PDO::FETCH_ASSOC);

        while ($dbRow = $dbQuery->fetch(PDO::FETCH_ASSOC))
        {
           $username=$dbRow["username"];
		   $fullname=$dbRow["fullname"];
		   $profileimage=$dbRow["profileimage"];
        }
	?>
	
    <title><?php echo $sitename;?> | Courses</title>
	
	<!--DK CSS-->
	<link href="../styles.css" rel="stylesheet">
	
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
			  <li class="nav-item active">
				<a class="nav-link" href="../course/">Courses</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link" href="../dashboard/">Dashboard</a>
			  </li>
			  <li class="nav-item">
				<a class="nav-link" href="../contact/">Contact</a>
			  </li>
			  <li class="nav-item">
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
		<br>

        <div class="container">

            <?php
            if (isset($_GET["course"]) && $_GET["course"]=="created")
            {
				echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
						<strong>Success!</strong> The course has been created.
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>';
            }
            if (isset($_GET["course"]) && $_GET["course"]=="noid")
            {
				echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<strong>ERROR!</strong> Invalid URL. No ID selected. Please contact the system administrator if this was a system fault.
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>';
            }
			if (isset($_GET["course"]) && $_GET["course"]=="hidden")
            {
				echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<strong>ERROR!</strong> Access denied. Please contact the system administrator if this was a system fault.
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>';
            }
			if (isset($_GET["unenrol"]) && $_GET["unenrol"]=="1")
            {
				echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
						<strong>Success!</strong> You have been unenrolled from the course.
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>';
            }
			if (isset($_GET["permission"]) && $_GET["permission"]=="0")
            {
				echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
						<strong>ERROR!</strong> Invalid permissions to access this page. Please contact the site administrator if this was a system fault.
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>';
            }
            ?>
            <h1>Course catalogue</h1>
			
			<div class="search">
				<form method="get" action="index.php">
						<div class="form-row">
							<div class="form-group col-md-9">
								<input class="form-control form-control-lg" type="text" name="search" placeholder="Search for courses">
							</div>
							
							<div class="form-group col-md-3">
								<button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-search"></i></button>
								<button onclick="window.location.href='../course'" class="btn btn-primary btn-lg"><i class="fas fa-times"></i></button>
								<?php if(has_capability("course:create",$userID)) { echo '<button type="button" class="btn btn-primary btn-lg" style="float:right" onclick="window.location.href=\'create.php\'">Add course</button>'; } ?>
							</div>
						</div>
				</form>
			</div>
			
                <table class="table">
	
                    <tr><th style="text-align:left;width:150px">Course</th><th style="text-align:left;max-width:500px">Description</th></tr>
                    <?php
					
					if (isset($_GET["search"]))
					{
						$search = $_GET["search"];
						
						$dbQuery=$db->prepare("select * from courses where `title` like :search");
						$dbParams=array('search'=>"%".$search."%");
						$dbQuery->execute($dbParams);
						$searchResults = $dbQuery->rowCount();
					}
					else
					{
						$dbQuery=$db->prepare("select * from courses");
						//$dbParams=array('id'=>$id);
						$dbQuery->execute();
					}
                   
                    while ($dbRow = $dbQuery->fetch(PDO::FETCH_ASSOC))
                    {
                        $courseID=$dbRow["id"];
                        $title=$dbRow["title"];
                        $description=$dbRow["description"];
                        $visibility=$dbRow["visibility"];

						if (has_capability("course:admin",$userID))
						{
							if ($visibility == "0")
							{
								echo "<tr> <td><a class='dimmed' href='view.php?id=".$courseID."'>".$title."</a></td> <td>".$description."</td></tr>";
							}
							else {
								echo "<tr> <td><a class='a' href='view.php?id=".$courseID."'>".$title."</a></td> <td>".$description."</td></tr>";
							}
						}
						else if ($visibility == "1")
						{
							echo "<tr> <td><a class='a' href='view.php?id=".$courseID."'>".$title."</a></td> <td>".$description."</td></tr>";	
						}
						else if ($visibility == "2")
						{
							// Check if user is enrolled on the restricted course
							if (isEnrolled($courseID, $userID))
							{
								echo "<tr> <td><a class='a' href='view.php?id=".$courseID."'>".$title."</a></td> <td>".$description."</td></tr>";
							}
						}
                    }
					
					echo "</table>";
					
					if ($searchResults == 0 && isset($_GET["search"]))
					{
						echo "<p class='search-results'>No courses found</p>";
					}
                    ?>

                

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
