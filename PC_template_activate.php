<h2>Please activate your registration</h2><b>Activation code was sent to the email address you specified..</b><br /><form method="post" action="<?php echo $this->site->Get_link()."activation/"; ?>">	<input type="text" name="activationCode" value="<?php echo v($_POST['activationCode']); ?>" />	<input type="submit" value="Activate" /></form>