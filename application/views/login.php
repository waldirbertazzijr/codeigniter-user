<form action="<?php echo site_url('login/validate') ?>" method="post">
    <p>
    User:<br />
    <input type="text" name="login" id="login" /></p>
    <p>
    Password:<br />
    <input type="password" name="password" id="password" /></p>
    <?php echo $this->session->flashdata('error_message');?>
    <?php echo $this->session->flashdata('success_message');?>
    
    <p><button value="send">Login</button></p>
    <p>Try it!<br/>
    User: admin<br/>
    Password: admin</p>
</form>
