<script type="text/javascript">
	$(function(){
		$('[name="_registerType"]').change(function(val){
			$('#pc_shop_form input[type="submit"]').show();
			var type = $(this).val();
			$('[id^="pc_shop_form_"]').hide();
			$("#pc_shop_form_"+ type).show();
		});
	});
</script>
<?php
$regResult = $this->site->Get_data('pc_shop_register_result');
if ($regResult !== false) {
	if (!v($regResult['success'])) {
		print_pre($regResult);
	}
}
$typeIsPerson = (v($_POST['_registerType']) == 'person');
$typeIsEntity = (v($_POST['_registerType']) == 'entity');

$loginError = in_array('login', v($regResult['errors'], array())) || in_array('login_exists', v($regResult['errors'], array()));
$emailError = in_array('email', v($regResult['errors'], array())) || in_array('account_exists', v($regResult['errors'], array()));
$passError = in_array('password', v($regResult['errors'], array()));
$pass2Error = in_array('retyped_password', v($regResult['errors'], array()));
$nameError = in_array('name', v($regResult['errors'], array()));

?>
<style type="text/css">label.invalid{color: red;}</style>
<h2>Already registered? Log in!</h2>
<form method="post">
	<label>Login:<br /><input type="text" name="user_login" value="<?php echo v($_POST['user_login']); ?>" /></label><br />
	<label>Password:<br /><input type="password" name="user_password" /></label><br />
	<input type="submit" value="Login" />
</form>
<h2>Register</h2>
<form method="post" id="pc_shop_form">
	<label<?php echo ($loginError?' class="invalid"':""); ?>>Prisijungimo vardas:<br /><input type="text" name="login" value="<?php echo v($_POST['login']); ?>" /></label><br />
	<label<?php echo ($emailError?' class="invalid"':""); ?>>El. paštas:<br /><input type="text" name="email" value="<?php echo v($_POST['email']); ?>" /></label><br />
	<label<?php echo ($passError?' class="invalid"':""); ?>>Slaptažodis:<br /><input type="password" name="pass" /></label><br />
	<label<?php echo ($pass2Error?' class="invalid"':""); ?>>Pakartokite slaptažodį:<br /><input type="password" name="pass2" /></label><br />
	<label>Pašto adresas:<br /><input type="text" name="address" value="<?php echo v($_POST['address']); ?>" /></label><br />
	<label>Telefono nr.:<br /><input type="text" name="phone" value="<?php echo v($_POST['phone']); ?>" /></label><br />
	<hr />
	<b>Kas jūs esate?</b>
	<label><input type="radio" name="_registerType" value="person"<?php echo ($typeIsPerson?" checked":"");?> /> Fizinis asmuo</label>
	<label><input type="radio" name="_registerType" value="entity"<?php echo ($typeIsEntity?" checked":"");?> /> Juridinis asmuo</label><hr />
	<div id="pc_shop_form_person"<?php if (!$typeIsPerson) echo ' style="display:none;"'; ?>>
		<label<?php echo ($nameError?' class="invalid"':""); ?>>Vardas, pavardė:<br /><input type="text" name="name" value="<?php echo v($_POST['name']); ?>" /></label>
	</div>
	<div id="pc_shop_form_entity"<?php if (!$typeIsEntity) echo ' style="display:none;"'; ?>>
		<label<?php echo ($nameError?' class="invalid"':""); ?>>Įmonės pavadinimas:<br /><input type="text" name="title" value="<?php echo v($_POST['title']); ?>" /></label><br />
		<label>Organizacija:<br /><input type="text" name="organization" value="<?php echo v($_POST['organization']); ?>" /></label><br />
		<label>Juridinis adresas:<br /><input type="text" name="domicile" value="<?php echo v($_POST['domicile']); ?>" /></label><br />
		<label>Mokesčių mokėtojo identifikacinis numeris:<br /><input type="text" name="inn" value="<?php echo v($_POST['inn']); ?>" /></label><br />
		<label>Priežasties registracijos kodas:<br /><input type="text" name="kpp" value="<?php echo v($_POST['kpp']); ?>" /></label><br />
		<label>Rusijos įmonių ir organizacijų klasifikacija:<br /><input type="text" name="okpo" value="<?php echo v($_POST['okpo']); ?>" /></label>
	</div>
	<input type="submit" name="register" value="Register"<?php if (!v($_POST['register'])) echo ' style="display:none;"'; ?> />
</form>