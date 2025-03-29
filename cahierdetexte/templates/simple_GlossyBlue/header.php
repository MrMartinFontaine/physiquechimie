<div id=header>
  <p>&nbsp;</p>
  <?php if (isset($page_accueil)&&($page_accueil==1)){ ?>
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td valign="middle"><div align="center">
          <?php if (isset($_SESSION['url_logo_etab'])&&($_SESSION['url_logo_etab']<>'')&&($_SESSION['url_logo_etab']<>'http://')){?>
          <a href="<?php echo $_SESSION['url_etab'];?>"><img src="<?php echo $_SESSION['url_logo_etab'];?> " border="0" /></a>
          <?php };?>
      </div></td>
      <td >
      <td ><div align="right"><div class="header_titre">Cahier de textes</div><br />
        <div class="header_description"> 
          <div align="center"><a href="<?php $_SESSION['nom_etab'];?>">
            <?php if(isset($_SESSION['nom_etab'])){echo $_SESSION['nom_etab'];};?>
            </a>
              <?php if(isset($_SESSION['identite'])){echo '  -  '.$_SESSION['identite'];};?>
              <br />
              <?php echo $header_description;?><br />
          </div>
        </div>
      </div></td>
    </tr>
  </table>
  <?php } else {

?>
  <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td><div align="center">
          <?php if (isset($_SESSION['url_logo_etab'])&&($_SESSION['url_logo_etab']<>'')&&($_SESSION['url_logo_etab']<>'http://')){?>
          <a href="../deconnexion.php"><img src="<?php echo $_SESSION['url_logo_etab'];?> " border="0" /></a>
          <?php };?>
        </div></td>
      <td><div align="center">
          <div class="header_titre">Cahier de textes</div>
          <br />
          <div class="header_description"> <a href="../deconnexion.php">
            <?php if(isset($_SESSION['nom_etab'])){echo $_SESSION['nom_etab'];};?>
            </a>
            <?php 
			if(isset($_SESSION['identite'])&&($_SESSION['droits']==2)){ 
						if(isset($_GET['vie_sco'])){echo '<a href="../enseignant/enseignant.php">'.$_SESSION['identite'].'</a>';} else {echo '    <a href="enseignant.php">'.$_SESSION['identite'].'</a>';};
			};?>
            <br />
            <?php echo $header_description;?><br />
          </div>
        </div></td>
    </tr>
  </table>
  <?php };?>
</div>
