<?php
echo '<h2 class="nav-tab-wrapper">';
foreach( $this->tabs as $tab => $name ){
$class = ( $tab == $this->active_page ) ? ' nav-tab-active' : '';
echo "<a class='nav-tab$class' href='?page=triplelift_np_admin&tab=$tab'>$name</a>";

}
echo '</h2><br>&nbsp;<br>';

