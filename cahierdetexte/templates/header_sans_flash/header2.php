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
      <td ><div align="right">

          <span align="center">
                <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
  codebase="http://active.macromedia.com/flash4/cabs/swflash.cab#version=4,0,0,0" width="474" height="100">
                  <param name="movie" value="templates/default/cdt.swf">
                  <param name="quality" value="high">
                  <param name="bgcolor" value="#FFFFFF">
                  <embed src="templates/default/cdt.swf" quality="high" bgcolor="#FFFFFF"
    width="474" height="100"
    type="application/x-shockwave-flash"
    pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"> </embed>
                </object>
          </span>
              <br />
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
          <a href="../../deconnexion.php"><img src="<?php echo $_SESSION['url_logo_etab'];?> " border="0" /></a>
          <?php };?>
        </div></td>
      <td><div align="center">
          <div class="header_titre">Cahier de textes</div>
          <br />
          <div class="header_description"> <a href="../../deconnexion.php">
            <?php if(isset($_SESSION['nom_etab'])){echo $_SESSION['nom_etab'];};?>
            </a>
            <?php if(isset($_SESSION['identite'])&&($_SESSION['droits']==2)){echo '  -  <a href="enseignant.php">'.$_SESSION['identite'].'</a>';};?>
            <br />
            <?php echo $header_description;?><br />
          </div>
        </div></td>
    </tr>
  </table>
  <?php };?>
</div>
