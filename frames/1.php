<?php
if(IN_MANAGER_MODE != "true") {
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
header("X-XSS-Protection: 0");
$_SESSION['browser'] = (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 1') !== false) ? 'legacy_IE' : 'modern';
$mxla = $modx_lang_attribute ? $modx_lang_attribute : 'en';
if(!isset($modx->config['manager_menu_height'])) {
	$modx->config['manager_menu_height'] = '48';
}
if(!isset($modx->config['manager_tree_width'])) {
	$modx->config['manager_tree_width'] = '320';
}
$modx->invokeEvent('OnManagerPreFrameLoader', array('action' => $action));

if(isset($_SESSION['onLoginForwardToAction']) && is_int($_SESSION['onLoginForwardToAction'])) {
	$initMainframeAction = $_SESSION['onLoginForwardToAction'];
	unset($_SESSION['onLoginForwardToAction']);
} else {
	$initMainframeAction = 2; // welcome.static
}
?>
	<!DOCTYPE html>
	<html <?php echo (isset($modx_textdir) && $modx_textdir ? 'dir="rtl" lang="' : 'lang="') . $mxla . '" xml:lang="' . $mxla . '"'; ?>>
	<head>
		<title><?php echo $site_name ?>- (MODX CMS Manager)</title>
		<meta name="viewport" content="width=device-width, minimum-scale=0.25, maximum-scale=1.0, initial-scale=0.8">
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $modx_manager_charset ?>" />
		<link rel="stylesheet" type="text/css" href="media/style/common/font-awesome/css/font-awesome.min.css" />
		<link rel="stylesheet" type="text/css" href="media/style/<?php echo $modx->config['manager_theme']; ?>/style.css" />
		<style>
			html, body { margin: 0; padding: 0; width: 100%; height: 100% }
			body { position: relative; background-color: #F2F2F2 !important; }
			#mainMenu, #tree, #main { position: absolute; }
			#mainMenu iframe, #tree iframe, #main iframe, #mask_resizer { position: absolute; width: 100%; height: 100%; }
			#mainMenu { position: relative; height: 48px; width: 100%; box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.25); z-index: 100; }
			#mainMenu iframe { height: 48px }
			#tree {
				width: <?php echo $modx->config['manager_tree_width'];
 ?>px; top: 48px; left: 0; bottom: 0; overflow: hidden; }
			#main { top: 48px; left: <?php echo $modx->config['manager_tree_width'];
 ?>px;
				right: 0; bottom: 0; }
			#resizer { position: absolute; top: 48px; bottom: 0; left: <?php echo $modx->config['manager_tree_width'];
 ?>px;
				width: 12px; cursor: col-resize; z-index: 999 }
			#resizer #hideMenu { position: absolute; z-index: 9; left: -12px; top: 5px; width: 24px; height: 24px; line-height: 24px; font-size: 20px; text-align: right; text-indent: -2px; cursor: pointer; content: "\f054" }
			.tree-open #resizer #hideMenu { text-align: center }
			.tree-open #resizer #hideMenu i::before { content: "\f053" }
			#resizer2 #hideTopMenu { display: block; margin-top: -4px; margin-left: 0; cursor: pointer; background: transparent url('media/style/<?php echo $modx->config['manager_theme'];
 ?>/images/icons/application_get.png') !important;
				width: 16px; height: 16px; }
		</style>
	</head>
	<body id="frameset" class="tree-open">
	<div id="resizer"><a id="hideMenu" onClick="mainMenu.toggleTreeFrame();"><i class="fa fa-chevron-right"></i></a>
	</div>
	<!--<div id="resizer2"> <a id="hideTopMenu" onClick="mainMenu.toggleMenuFrame();"></a> </div>-->
	<div id="mainMenu">
		<iframe name="mainMenu" src="index.php?a=1&amp;f=menu" scrolling="no" frameborder="0" noresize="noresize"></iframe>
	</div>
	<div id="tree">
		<iframe name="tree" src="index.php?a=1&amp;f=tree" scrolling="no" frameborder="0" onresize="mainMenu.resizeTree();"></iframe>
	</div>
	<div id="main">
		<iframe name="main" id="mainframe" src="index.php?a=<?php echo $initMainframeAction; ?>" scrolling="auto" frameborder="0" onload="if (document.mainMenu.stopWork()) document.mainMenu.stopWork(); scrollWork();"></iframe>
	</div>
	<div class="dropdown"></div>
	<script language="JavaScript" type="text/javascript">
		var _startY = 48;
		var _dragElement;
		var _oldZIndex = 999;
		var _left;
		var mask = document.createElement('div');
		mask.id = 'mask_resizer';
		mask.style.zIndex = _oldZIndex;

		InitDragDrop();

		function InitDragDrop() {
			document.getElementById('resizer').onmousedown = OnMouseDown;
			document.getElementById('resizer').onmouseup = OnMouseUp
		}

		function OnMouseDown(e) {
			if(e == null) e = window.event;
			_dragElement = e.target != null ? e.target : e.srcElement;
			if((e.button == 1 && window.event != null || e.button == 0) && _dragElement.id == 'resizer') {
				_oldZIndex = _dragElement.style.zIndex;
				_dragElement.style.zIndex = 10000;
				_dragElement.style.background = '#444';
				document.body.appendChild(mask);
				document.onmousemove = OnMouseMove;
				document.body.focus();
				document.onselectstart = function() {
					return false
				};
				_dragElement.ondragstart = function() {
					return false
				};
				return false
			}
		}

		function ExtractNumber(value) {
			var n = parseInt(value);
			return n == null || isNaN(n) ? 0 : n
		}

		function OnMouseMove(e) {
			if(e == null) var e = window.event;
			_dragElement.style.left = e.clientX + 'px';
			_dragElement.style.top = _startY + 'px';
			document.getElementById('tree').style.width = e.clientX + 'px';
			document.getElementById('main').style.left = e.clientX + 'px'
		}

		function OnMouseUp(e) {
			if(_dragElement != null) {
				_dragElement.style.zIndex = _oldZIndex;
				_dragElement.style.background = 'transparent';
				_dragElement.ondragstart = null;
				_dragElement = null;
				document.onmousemove = null;
				document.onselectstart = null
				document.body.removeChild(mask);
			}
		}

		//save scrollPosition
		function getQueryVariable(variable, query) {
			var vars = query.split('&');
			for(var i = 0; i < vars.length; i++) {
				var pair = vars[i].split('=');
				if(decodeURIComponent(pair[0]) == variable) {
					return decodeURIComponent(pair[1]);
				}
			}
		}

		function scrollWork() {
			var frm = document.getElementById("mainframe").contentWindow;
			currentPageY = localStorage.getItem('page_y');
			pageUrl = localStorage.getItem('page_url');
			if(currentPageY === undefined) {
				localStorage.setItem('page_y', 0);
			}
			if(pageUrl === null) {
				pageUrl = frm.location.search.substring(1);
			}
			if(getQueryVariable('a', pageUrl) == getQueryVariable('a', frm.location.search.substring(1))) {
				if(getQueryVariable('id', pageUrl) == getQueryVariable('id', frm.location.search.substring(1))) {
					frm.scrollTo(0, currentPageY);
				}
			}

			frm.onscroll = function() {
				if(frm.pageYOffset > 0) {
					localStorage.setItem('page_y', frm.pageYOffset);
					localStorage.setItem('page_url', frm.location.search.substring(1));
				}
			}
		}
	</script>
	<?php
	$modx->invokeEvent('OnManagerFrameLoader', array('action' => $action));
	?>
	</body>
	</html>
<?php
