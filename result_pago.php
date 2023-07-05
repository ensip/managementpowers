<?php

syslog(LOG_INFO, __FILE__.": GET: ".print_r($_GET, true));
syslog(LOG_INFO, __FILE__.": POST: ".print_r($_POST, true));

$ref = null;
$order = null;
$intentos_max = 10;

if (isset($_GET['ref'])) {
	$ref = $_GET['ref'];

}
?>

<!DOCTYPE html>
<!--[if IE 6]>
<html class="ie lte-ie9 lte-ie8 lte-ie7 ie6" lang="en-US">
<![endif]-->
<!--[if IE 7]>
<html class="ie lte-ie9 lte-ie8 lte-ie7 ie7" lang="en-US">
<![endif]-->
<!--[if IE 8]>
<html class="ie lte-ie9 lte-ie8 ie8" lang="en-US">
<![endif]-->
<!--[if IE 9]>
<html class="ie lte-ie9 ie9" lang="en-US">
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8) | !(IE 9)  ]><!-->
<html lang="en-US">
<!--<![endif]-->
<head><script>if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}</script><script>class RocketLazyLoadScripts{constructor(e){this.triggerEvents=e,this.eventOptions={passive:!0},this.userEventListener=this.triggerListener.bind(this),this.delayedScripts={normal:[],async:[],defer:[]},this.allJQueries=[]}_addUserInteractionListener(e){this.triggerEvents.forEach((t=>window.addEventListener(t,e.userEventListener,e.eventOptions)))}_removeUserInteractionListener(e){this.triggerEvents.forEach((t=>window.removeEventListener(t,e.userEventListener,e.eventOptions)))}triggerListener(){this._removeUserInteractionListener(this),"loading"===document.readyState?document.addEventListener("DOMContentLoaded",this._loadEverythingNow.bind(this)):this._loadEverythingNow()}async _loadEverythingNow(){this._delayEventListeners(),this._delayJQueryReady(this),this._handleDocumentWrite(),this._registerAllDelayedScripts(),this._preloadAllScripts(),await this._loadScriptsFromList(this.delayedScripts.normal),await this._loadScriptsFromList(this.delayedScripts.defer),await this._loadScriptsFromList(this.delayedScripts.async),await this._triggerDOMContentLoaded(),await this._triggerWindowLoad(),window.dispatchEvent(new Event("rocket-allScriptsLoaded"))}_registerAllDelayedScripts(){document.querySelectorAll("script[type=rocketlazyloadscript]").forEach((e=>{e.hasAttribute("src")?e.hasAttribute("async")&&!1!==e.async?this.delayedScripts.async.push(e):e.hasAttribute("defer")&&!1!==e.defer||"module"===e.getAttribute("data-rocket-type")?this.delayedScripts.defer.push(e):this.delayedScripts.normal.push(e):this.delayedScripts.normal.push(e)}))}async _transformScript(e){return await this._requestAnimFrame(),new Promise((t=>{const n=document.createElement("script");let i;[...e.attributes].forEach((e=>{let t=e.nodeName;"type"!==t&&("data-rocket-type"===t&&(t="type",i=e.nodeValue),n.setAttribute(t,e.nodeValue))})),e.hasAttribute("src")&&this._isValidScriptType(i)?(n.addEventListener("load",t),n.addEventListener("error",t)):(n.text=e.text,t()),e.parentNode.replaceChild(n,e)}))}_isValidScriptType(e){return!e||""===e||"string"==typeof e&&["text/javascript","text/x-javascript","text/ecmascript","text/jscript","application/javascript","application/x-javascript","application/ecmascript","application/jscript","module"].includes(e.toLowerCase())}async _loadScriptsFromList(e){const t=e.shift();return t?(await this._transformScript(t),this._loadScriptsFromList(e)):Promise.resolve()}_preloadAllScripts(){var e=document.createDocumentFragment();[...this.delayedScripts.normal,...this.delayedScripts.defer,...this.delayedScripts.async].forEach((t=>{const n=t.getAttribute("src");if(n){const t=document.createElement("link");t.href=n,t.rel="preload",t.as="script",e.appendChild(t)}})),document.head.appendChild(e)}_delayEventListeners(){let e={};function t(t,n){!function(t){function n(n){return e[t].eventsToRewrite.indexOf(n)>=0?"rocket-"+n:n}e[t]||(e[t]={originalFunctions:{add:t.addEventListener,remove:t.removeEventListener},eventsToRewrite:[]},t.addEventListener=function(){arguments[0]=n(arguments[0]),e[t].originalFunctions.add.apply(t,arguments)},t.removeEventListener=function(){arguments[0]=n(arguments[0]),e[t].originalFunctions.remove.apply(t,arguments)})}(t),e[t].eventsToRewrite.push(n)}function n(e,t){const n=e[t];Object.defineProperty(e,t,{get:n||function(){},set:n=>{e["rocket"+t]=n}})}t(document,"DOMContentLoaded"),t(window,"DOMContentLoaded"),t(window,"load"),t(window,"pageshow"),t(document,"readystatechange"),n(document,"onreadystatechange"),n(window,"onload"),n(window,"onpageshow")}_delayJQueryReady(e){let t=window.jQuery;Object.defineProperty(window,"jQuery",{get:()=>t,set(n){if(n&&n.fn&&!e.allJQueries.includes(n)){n.fn.ready=n.fn.init.prototype.ready=function(t){e.domReadyFired?t.bind(document)(n):document.addEventListener("rocket-DOMContentLoaded",(()=>t.bind(document)(n)))};const t=n.fn.on;n.fn.on=n.fn.init.prototype.on=function(){if(this[0]===window){function e(e){return e.split(" ").map((e=>"load"===e||0===e.indexOf("load.")?"rocket-jquery-load":e)).join(" ")}"string"==typeof arguments[0]||arguments[0]instanceof String?arguments[0]=e(arguments[0]):"object"==typeof arguments[0]&&Object.keys(arguments[0]).forEach((t=>{delete Object.assign(arguments[0],{[e(t)]:arguments[0][t]})[t]}))}return t.apply(this,arguments),this},e.allJQueries.push(n)}t=n}})}async _triggerDOMContentLoaded(){this.domReadyFired=!0,await this._requestAnimFrame(),document.dispatchEvent(new Event("rocket-DOMContentLoaded")),await this._requestAnimFrame(),window.dispatchEvent(new Event("rocket-DOMContentLoaded")),await this._requestAnimFrame(),document.dispatchEvent(new Event("rocket-readystatechange")),await this._requestAnimFrame(),document.rocketonreadystatechange&&document.rocketonreadystatechange()}async _triggerWindowLoad(){await this._requestAnimFrame(),window.dispatchEvent(new Event("rocket-load")),await this._requestAnimFrame(),window.rocketonload&&window.rocketonload(),await this._requestAnimFrame(),this.allJQueries.forEach((e=>e(window).trigger("rocket-jquery-load"))),window.dispatchEvent(new Event("rocket-pageshow")),await this._requestAnimFrame(),window.rocketonpageshow&&window.rocketonpageshow()}_handleDocumentWrite(){const e=new Map;document.write=document.writeln=function(t){const n=document.currentScript,i=document.createRange(),r=n.parentElement;let a=e.get(n);void 0===a&&(a=n.nextSibling,e.set(n,a));const o=document.createDocumentFragment();i.setStart(o,0),o.appendChild(i.createContextualFragment(t)),r.insertBefore(o,a)}}async _requestAnimFrame(){return new Promise((e=>requestAnimationFrame(e)))}static run(){const e=new RocketLazyLoadScripts(["keydown","mousemove","touchmove","touchstart","touchend","touchcancel","touchforcechange","wheel"]);e._addUserInteractionListener(e)}}RocketLazyLoadScripts.run();
</script>
	<meta charset="UTF-8" />

	<meta name="HandheldFriendly" content="true" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="profile" href="http://gmpg.org/xfn/11" />

	<meta name="author" content="Management Powers" />

	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600&#038;display=swap" rel="stylesheet">

<!--[if lt IE 9]>
<script type="text/javascript" src="./js/html5.js" ></script>
<![endif]-->

	<link rel="icon" sizes="228x228" href="./img/favicon/coast-icon-228x228.png">

	<meta name='robots' content='max-image-preview:large' />

	<!-- This site is optimized with the Yoast SEO plugin v15.5 - https://yoast.com/wordpress/plugins/seo/ -->
	<title>CRM by Management Powers: a comprehensive range of complementary solutions</title>
	<link rel="stylesheet" href="./css/7856cf781c26a3160a06f572a12a2da1.css" media="all" data-minify="1" />
	<meta name="description" content="A CRM solution for each business sector. Thanks to our range of specialised versions, you will find the CRM solution adapted to your needs." />
	<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />


<link href='https://fonts.gstatic.com' crossorigin rel='preconnect' />

<script type='text/javascript' src='./js/jquery.min.js?ver=3.5.1' id='jquery-core-js'></script>
<script type='text/javascript' src='./js/jquery-migrate.min.js?ver=3.3.2' id='jquery-migrate-js' defer></script>
<link rel="canonical" href="https://www.managementpowers.com/" /><noscript><style id="rocket-lazyload-nojs-css">.rll-youtube-player, [data-lazy-src]{display:none !important;}</style></noscript>

</head>

<body class="page-template-default page page-id-8589 en">

<?php

if ($ref) {
	for ($i = 1; $i < $intentos_max; $i++) {
		if (file_exists("/var/www/managementpowers/tmp/tokens_managementpowers/$ref.txt")) {
			$order = unserialize(file_get_contents("/var/www/managementpowers/tmp/tokens_managementpowers/$ref.txt"));
			syslog(LOG_INFO, __FILE__.": orden encontrada en tmp/tokens_managementpowers/$ref.txt");
			break;
		}
		syslog(LOG_INFO, __FILE__.": no encontrada orden en tmp/tokens_managementpowers/$ref.txt");
		sleep(1);
	}

	if ($order) {
		if ($order->order->_embedded->payment[0]->state != "FAILED") {
			echo "<h3>Your order has been correctly paid</h3><p>We will contact you before 24 hours. Thank you for trusting us!</p>";
		} else {
			echo "<h3>Your payment has failed</h3><p>Please, try again or contact us at <a href='mailto:info@managementpowers.com'>info@managementpowers.com</a></p>";
		}
	} else {
		echo "<h3>Your payment has failed</h3><p>Please, try again or contact us at <a href='mailto:info@managementpowers.com'>info@managementpowers.com</a></p>";
	}
}

?>

</body>

</html>
