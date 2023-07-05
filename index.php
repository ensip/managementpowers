<?php
	syslog(LOG_INFO, __FILE__.": GET: ".print_r($_GET, true));
	syslog(LOG_INFO, __FILE__.": POST: ".print_r($_POST, true));
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
		
<section class="header-hero">
	<div class="ribbon">
				<header id="header-main" role="banner">
			<div class="header-wrapper">
				<div class="header-inner">
					<div id="branding">
						<strong>
								<a href="./" title="Management Powers" rel="home">
									Management Powers - 								</a>
						</strong>
					</div>
					<div class="trigger-menu">
						<button class="burger-menu" role="widget">
							<span>Open-Close the menu</span>
						</button>
					</div>
					<div class="phone-menu-mobile-wrapper">
					<nav id="access" role="navigation">
							<div class="menu-main-en-container"><ul id="menu-main-en" class="menu">
							<li id="menu-item-8921" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-8921"><a href="./departments.html">Roles &#038; Industries</a></li>
							<li id="menu-item-8923" class="menu-item menu-item-type-post_type menu-item-object-page current-menu-item page_item page-item-8589 current_page_item current-menu-ancestor current-menu-parent current_page_parent current_page_ancestor menu-item-has-children menu-item-8923"><a href="./" aria-current="page">Price</a></li>
							<li id="menu-item-40016" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-40016"><a href="./features.html">Features</a></li>
							<li id="menu-item-18250" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-18250"><a href="./about-us.html">About us</a></li>
							<li id="menu-item-25374" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-has-children menu-item-25374"><a href="./resources.html">Resources</a></li>
							<li id="menu-item-40011" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-40011"><a href="./contact.html">Contact</a></li>
						</ul></div>			
					</nav><!-- #access -->
					<a href="#" class="btn"><span>TRY IT</span></a>

						<div class="search-form-wrapper fade">
	<div class="close">X</div>
	<div class="content-search">
		<p>What are you looking for?</p>
		<form role="search" method="get" class="search-form" action="./">
			<label class="screen-reader-text">Search for:</label>
			<input type="search" class="search-field" placeholder="Search ..." value="" name="s" title="Search for:" />
			<div class="wrapper-search-submit">
				<input type="submit" class="search-submit" value="Search" />
			</div>
		</form>
		<div>
					</div>
	</div>
</div>
					</div>
				</div>
				<div class="breadcrump">
					<a href="./">Home</a>  <span>Our range of CRM solutions</span>				
				</div>
					</div>
	</header><!-- #header-main -->
</div>

		<div class="hero">
			<div class="txt">
			<h1>Our range of CRM solutions</h1>
			<div class="intro">
			<p>Revolutionise your Customer Relationship with our CRM solutions! Our range means that each company will be able to find the CRM solution adapted to its needs. Moreover, because we are more than just a publisher, we will support you throughout your project.</p>
			</div>
			</div>
			<div class="img" style="margin-bottom:50px;">
					<img src="./img/svg/managementpowers-intro.svg" title="Prices - Management Powers"/>
			</div>
</div>
							</section> <!-- header-hero -->
			<main role="main" class="hfeed site">
<div id="primary" class="content-area">
	<article id="post-8589" class="post-8589 page type-page status-publish hentry">

			<div class="content-main">
							</div>
												
<section class="section-flexible section-html section-products">
		<h2>Your tailor-made CRM</h2>
	<section class="products">
		<div class="products-wrapper">
			
			<article id="post-52981" class="post-52981 products type-products status-publish hentry" >
					<div class="picto">
					<img src="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%200%200'%3E%3C/svg%3E" alt="picto" data-lazy-src="./img/picto/white-hands-icon.svg">
					<noscript>
						<img src="./img/picto/white-hands-icon.svg" alt="picto">
					</noscript>
				</div>
				<header class="entry-header" style="">
					<h3 class="entry-title">
						<a href="#" title="Permalink to Starter" rel="bookmark">Starter</a>
					</h3>
				</header>
				<div class="content" itemprop="description">
					<div class="text">Management Powers CRM Starter will be your first CRM! Ready-to-use, it enables your sales to enhance your turnover and gain productivity.</div>
					<div class="price">
					20€ or 86AED / user / month<p style="font-size:12px;text-align: center;margin-bottom:5px;"> (2 users minimum)</p>		
						<small style="font-size:12px;text-align: center;font-weight:bold;"><em>*Amount non-refundable</em></small>	
					</div>
					<a href="./features.html" class="read-more">See the features</a>
				    <div class="after-cta-comment">
				  <p class="product-option"><strong>Available complements:</strong><br />
			Marketing, invoicing</p>

			<p><a href="form_purchase.php?t=starter" class="btn"><span>Purchase It</span></a></p>
				</div>
				</div>

			</article>

<article id="post-12626" class="post-12626 products type-products status-publish hentry" >
		<div class="picto">
		<img src="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%200%200'%3E%3C/svg%3E" alt="picto" data-lazy-src="./img/picto/white-conversation-icon_2.svg"><noscript>
		<img src="./img/picto/white-conversation-icon_2.svg" alt="picto"></noscript>
	</div>
	<header class="entry-header" style="">
		<h3 class="entry-title">
			<a href="#" title="Permalink to SMB" rel="bookmark">SMB</a>
		</h3>
	</header>
	<div class="content" itemprop="description">
		<div class="text">This is highly configurable CRM that covers the needs of your business. Also is the perfect for your sales team.</div>
		<div class="price">
			40€ or 172AED / user / month<p style="font-size:12px;text-align: center;margin-bottom:5px;"> (5 users minimum)</p>		
			<small style="font-size:12px;text-align: center;font-weight:bold;"><em>*Amount non-refundable</em></small>	
		</div>
		<a href="./features.html" class="read-more">See the features</a>
            <div class="after-cta-comment">
          <p class="product-option"><strong>Available complements:</strong><br />
Marketing, invoicing, project management</p>
<p><a href="form_purchase.php?t=smb" class="btn"><span>Purchase It</span></a></p>
        </div>
    	</div>
</article>

<article id="post-12633" class="post-12633 products type-products status-publish hentry" >
		<div class="picto">
		<img src="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%200%200'%3E%3C/svg%3E" alt="picto" data-lazy-src="./img/picto/white-synchro-icon.svg"><noscript>
		<img src="./img/picto/white-synchro-icon.svg" alt="picto"></noscript>
	</div>
	<header class="entry-header" style="">
		<h3 class="entry-title">
			<a href="#" title="Permalink to Enterprise" rel="bookmark">Enterprise</a>
		</h3>
	</header>
	<div class="content" itemprop="description">
		<div class="text">You need a complete CRM (sales, marketing, customer service, timesheet) that enables you to centralize all customer knowledge.</div>
		<div class="price">
			60€ or 258AED / user / month<p style="font-size:12px;text-align: center;margin-bottom:5px;"> (5 users minimum)</p>		
			<small style="font-size:12px;text-align: center;font-weight:bold;"><em>*Amount non-refundable</em></small>	
		</div>
		<a href="./features.html" class="read-more">See the features</a>
            <div class="after-cta-comment">
          <p class="product-option"><strong>Available complements:</strong><br />
advanced project management, invoicing</p>
<p><a href="form_purchase.php?t=enterprise" class="btn"><span>Purchase It</span></a></p>
        </div>
    	</div>
</article>

<article id="post-9392" class="post-9392 products type-products status-publish hentry" >
		<div class="picto">
		<img src="data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%200%200'%3E%3C/svg%3E" alt="picto" data-lazy-src="./img/picto/white-speedometer-icon.svg"><noscript><img src="./img/picto/white-speedometer-icon.svg" alt="picto"></noscript>
	</div>
	<header class="entry-header" style="">
		<h3 class="entry-title">
			<a href="#" title="Permalink to Corporate" rel="bookmark">Corporate</a>
		</h3>
	</header>
	<div class="content" itemprop="description">
		<div class="text">This is your first CRM project, so you have numerous customer processes. With this edition, the BPM is just one click away.</div>
		<div class="price">
			80€ or 345AED / user / month<p style="font-size:12px;text-align: center;margin-bottom:5px;"> (10 users minimum)</p>		
			<small style="font-size:12px;text-align: center;font-weight:bold;"><em>*Amount non-refundable</em></small>	
		</div>
		<a href="./features.html" class="read-more">See the features</a>
            <div class="after-cta-comment">
          <p class="product-option"><strong>Available complements:</strong><br />
advanced Marketing</p>
	<p><a href="form_purchase.php?t=corporate" class="btn"><span>Purchase It</span></a></p>
        </div>
    	</div>

</article>

		</div>
	</section>


	
</section>
									
<section class="section-flexible section-html section-products">
			<section class="tableau-products">
		<h2>Comparative table of our CRM solutions</h2>
		<div id="products-comparator">
			<div class="comparator-labels">
				<ul>
					<li>Sales</li><li>Invoicing</li><li>Marketing</li><li>Advanced Marketing</li><li>KPI</li><li>Mobile app</li><li>Customer Service</li><li>Project Management </li><li>Advanced Project Management </li><li>Gamification</li><li>CRM Coaching</li><li>Highly configurable</li><li>Highly customizable</li><li>Business Process Management</li><li>Upgrade & Maintenance included</li><li>Management Powers Connect</li>
				</ul>
			</div>
			<div class="comparator-products">
				<ul class="parent-flex">
											<li>
							<header>
								<div>
									<h2>Starter</h2>
								</div>
							</header>
							<ul class="advantages">
								<li class="yes-or-no"><span class="label">●</span><p>Sales</p></li>										              
								<li class="yes-or-no"><span class="label label-option">Option</span><p>Invoicing</p></li>
								<li class="yes-or-no"><span class="label label-option">Option</span><p>Marketing</p></li>
								<li class="yes-or-no hidden-sm hidden-xs"><p>Advanced Marketing</p></li>										
								<li class="yes-or-no"><span class="label">●</span><p>KPI</p></li>										    
								<li class="yes-or-no"><span class="label">●</span><p>Mobile app</p></li>									
								<li class="yes-or-no hidden-sm hidden-xs"><p>Customer Service</p></li>										  
								<li class="yes-or-no hidden-sm hidden-xs"><p>Project Management </p></li>										
								<li class="yes-or-no hidden-sm hidden-xs"><p>Advanced Project Management </p></li>								
								<li class="yes-or-no hidden-sm hidden-xs"><p>Gamification</p></li>										       
								<li class="yes-or-no"><span class="label label-option">Option</span><p>CRM Coaching</p></li>			
								<li class="yes-or-no hidden-sm hidden-xs"><p>Highly configurable</p></li>									
								<li class="yes-or-no hidden-sm hidden-xs"><p>Highly customizable</p></li>									
								<li class="yes-or-no hidden-sm hidden-xs"><p>Business Process Management</p></li>								
								<li class="yes-or-no"><span class="label">●</span><p>Upgrade & Maintenance included</p></li>
								<li class="yes-or-no"><span class="label">●</span><p>Management Powers Connect</p></li>							
							</ul>
				</li>
				<li>
					<header>
						<div>
							<h2>SMB</h2>
						</div>
					</header>
					<ul class="advantages">
								<li class="yes-or-no"><span class="label">●</span><p>Sales</p></li>										                  
								<li class="yes-or-no"><span class="label label-option">Option</span><p>Invoicing</p></li>										            
								<li class="yes-or-no"><span class="label label-option">Option</span><p>Marketing</p></li>										             
								<li class="yes-or-no hidden-sm hidden-xs"><p>Advanced Marketing</p></li>										                  									                    
								<li class="yes-or-no"><span class="label">●</span><p>KPI</p></li>										                  									                    
								<li class="yes-or-no"><span class="label">●</span><p>Mobile app</p></li>										                  									                    
								<li class="yes-or-no"><span class="label label-option">Option</span><p>Customer Service</p></li>										                             
								<li class="yes-or-no"><span class="label label-option">Option</span><p>Project Management </p></li>										                
								<li class="yes-or-no hidden-sm hidden-xs"><p>Advanced Project Management </p></li>										                  									                    <li class="yes-or-no hidden-sm hidden-xs"><p>Gamification</p></li>										                  									                    
								<li class="yes-or-no"><span class="label label-option">Option</span><p>CRM Coaching</p></li>										                  							
								<li class="yes-or-no"><span class="label">●</span><p>Highly configurable</p></li>										                  									                    <li class="yes-or-no hidden-sm hidden-xs"><p>Highly customizable</p></li>										                  									                    
								<li class="yes-or-no hidden-sm hidden-xs"><p>Business Process Management</p></li>										                  									                    <li class="yes-or-no"><span class="label">●</span><p>Upgrade & Maintenance included</p></li>										                  						
								<li class="yes-or-no"><span class="label">●</span><p>Management Powers Connect</p></li>										          
						</ul>
              </li>
              						<li>
							<header>
								<div>
									<h2>Enterprise</h2>
								</div>
							</header>
							<ul class="advantages">
							<li class="yes-or-no"><span class="label">●</span><p>Sales</p></li>										                  									                   
							<li class="yes-or-no"><span class="label label-option">Option</span><p>Invoicing</p></li>										                  							
							<li class="yes-or-no"><span class="label">●</span><p>Marketing</p></li>									
							<li class="yes-or-no"><span class="label label-option">Option</span><p>Advanced Marketing</p></li>										                  						
							<li class="yes-or-no"><span class="label">●</span><p>KPI</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Mobile app</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Customer Service</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Project Management </p></li>										                  									                    
							<li class="yes-or-no"><span class="label label-option">Option</span><p>Advanced Project Management </p></li>										            
							<li class="yes-or-no"><span class="label label-option">Option</span><p>Gamification</p></li>										                  								
							<li class="yes-or-no"><span class="label">●</span><p>CRM Coaching</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Highly configurable</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Highly customizable</p></li>										                  									                    
							<li class="yes-or-no hidden-sm hidden-xs"><p>Business Process Management</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Upgrade & Maintenance included</p></li>										                  									 
							<li class="yes-or-no"><span class="label">●</span><p>Management Powers Connect</p></li>										                                  
							</ul>
              </li>
              						<li>
							<header>
								<div>
									<h2>Corporate</h2>
								</div>
							</header>
							<ul class="advantages">
							<li class="yes-or-no"><span class="label">●</span><p>Sales</p></li>										                  									                    
							<li class="yes-or-no hidden-sm hidden-xs"><p>Invoicing</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Marketing</p></li>										                  									                    
							<li class="yes-or-no"><span class="label label-option">Option</span><p>Advanced Marketing</p></li>
							<li class="yes-or-no"><span class="label">●</span><p>KPI</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Mobile app</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Customer Service</p></li>										                  									                    
							<li class="yes-or-no hidden-sm hidden-xs"><p>Project Management </p></li>										                  									                    
							<li class="yes-or-no hidden-sm hidden-xs"><p>Advanced Project Management </p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Gamification</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>CRM Coaching</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Highly configurable</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Highly customizable</p></li>										                  									                    
							<li class="yes-or-no"><span class="label">●</span><p>Business Process Management</p></li>
							<li class="yes-or-no"><span class="label">●</span><p>Upgrade & Maintenance included</p></li>
							<li class="yes-or-no"><span class="label">●</span><p>Management Powers Connect</p></li>										                                  
							</ul>
              </li>
              
						</ul>
					</div>

				</div>
			</section>
			
</section>
										

<section class="section-flexible section-imagetext bg-color">
	<div class="inner">
						<figure data-bg="./img/meeting-work.jpg" class="bg-img rocket-lazyload" style=""></figure>

					<div class="content">
							<h2>Operational features</h2>
						<div class="text">
				<p><span>Our CRM solutions offer no less than 200 operational features. </span></p>
<p><span>Because we co-construct our solutions with our customers, these features meet the real needs of sales representatives, marketers, call centre agents and managers. </span></p>
<p><a href="./features.html" class="btn"><span>Discover our features</span></a></p>
			</div>
		</div>
	</div>
</section>
										

<section class="section-flexible section-textimage bg-color">
	<div class="inner">
		<div class="content">
							<h2>Robust CRM solutions</h2>
						<div class="text">
				<p>Because the CRM solution is often the cornerstone of the customer information system, it is important for your information system manager to be able to work with the CRM solution easily.</p>
<p>Our solutions are open, flexible and robust.</p>
<p>They are also scalable and support the growth of your company.</p>
<p>This makes them easy to integrate with your information system.</p>
<p><a href="#" class="btn"><span>Discover the technical characteristics</span></a></p>
			</div>
		</div>

		<figure data-bg="./img/chief-technical-officer-.jpg" class="bg-img rocket-lazyload" style=""></figure>

				</div>
</section>

							
	</article>
	</div><!-- #primary -->


</main>
	<section class="footer-big">
			<section class="ask-for-demo">
		<p>Get a free demo</p>
		<a href="./" class="btn"><span>Yes, I want a demo</span></a>
	</section>

</section>
<footer id="footer-main">
	<div id="colophon" role="contentinfo">
		<div class="wrapper-credit">
			<p class="credit">© Copyright 2021 Management Powers - All rights reserved</p>
			<nav class="gdpr-menu" role="navigation">
				<div class="menu-gdpr-menu-container"><ul id="menu-gdpr-menu" class="menu">
					<li id="menu-item-6" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-6"><a href="./terms-and-conditions.html">Terms of use</a></li>
					<li id="menu-item-72" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-72"><a href="./privacy-policy.html">Privacy Policy</a></li>
					<li id="menu-item-10" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-10"><a href="./cookie-policy.html">Cookie Policy</a></li>
			</ul></div>			
			</nav>
		</div>
	</div>
	<p style="text-align:center">Management Powers refers to Exitel FZCO DSO-IFZA Dubai Silicon Oasis License No 7139 - Dubai - UAE.</p>
</footer><!-- footer-main -->

<!--[if (lt IE 9) & (!IEMobile)]>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/respond.js/1.3.0/respond.js" ></script>
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/nwmatcher/1.2.5/nwmatcher.min.js"></script>
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/selectivizr/1.0.2/selectivizr-min.js" ></script>
	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/placeholders/2.1.0/placeholders.js" ></script>
<![endif]-->


<script data-minify="1" type='text/javascript' src='./js/modernizr-custom.js?ver=1627389757' id='modernizr-js' defer></script>
<script type='text/javascript' src='./js/owl.carousel.min.js' id='gbl-owl-js' defer></script>

<script type='text/javascript' src='./js/main.min.js' id='gbl-toolbox-js' defer></script>

<script type='text/javascript' src='./js/wp-embed.min.js?ver=5.7.2' id='wp-embed-js' defer></script>
<script type='text/javascript' id='cookie-agreed-js-extra'>
/* <![CDATA[ */
var cookie = {"validity":"360"};
/* ]]> */
</script>
<script data-minify="1" type='text/javascript' src='./js/cookies_disclaimer.js?ver=1627389757' id='cookie-agreed-js' defer></script>
<script data-minify="1" type='text/javascript' src='./js/helper.js?ver=1627389757' id='optinmonster-wp-helper-js' defer></script>
<script data-minify="1" type='text/javascript' src='./js/jquery-match-height.js?ver=1627389757' id='matchHeight-js' defer></script>

<script>window.lazyLoadOptions={elements_selector:"img[data-lazy-src],.rocket-lazyload,iframe[data-lazy-src]",data_src:"lazy-src",data_srcset:"lazy-srcset",data_sizes:"lazy-sizes",class_loading:"lazyloading",class_loaded:"lazyloaded",threshold:300,callback_loaded:function(element){if(element.tagName==="IFRAME"&&element.dataset.rocketLazyload=="fitvidscompatible"){if(element.classList.contains("lazyloaded")){if(typeof window.jQuery!="undefined"){if(jQuery.fn.fitVids){jQuery(element).parent().fitVids()}}}}}};window.addEventListener('LazyLoad::Initialized',function(e){var lazyLoadInstance=e.detail.instance;if(window.MutationObserver){var observer=new MutationObserver(function(mutations){var image_count=0;var iframe_count=0;var rocketlazy_count=0;mutations.forEach(function(mutation){for(i=0;i<mutation.addedNodes.length;i++){if(typeof mutation.addedNodes[i].getElementsByTagName!=='function'){continue}
if(typeof mutation.addedNodes[i].getElementsByClassName!=='function'){continue}
images=mutation.addedNodes[i].getElementsByTagName('img');is_image=mutation.addedNodes[i].tagName=="IMG";iframes=mutation.addedNodes[i].getElementsByTagName('iframe');is_iframe=mutation.addedNodes[i].tagName=="IFRAME";rocket_lazy=mutation.addedNodes[i].getElementsByClassName('rocket-lazyload');image_count+=images.length;iframe_count+=iframes.length;rocketlazy_count+=rocket_lazy.length;if(is_image){image_count+=1}
if(is_iframe){iframe_count+=1}}});if(image_count>0||iframe_count>0||rocketlazy_count>0){lazyLoadInstance.update()}});var b=document.getElementsByTagName("body")[0];var config={childList:!0,subtree:!0};observer.observe(b,config)}},!1)</script>
<script data-no-minify="1" async src="./js/lazyload.min.js"></script>

<script>function lazyLoadThumb(e){var t='<img loading="lazy" data-lazy-src="https://i.ytimg.com/vi/ID/hqdefault.jpg" alt="" width="480" height="360"><noscript><img src="https://i.ytimg.com/vi/ID/hqdefault.jpg" alt="" width="480" height="360"></noscript>',a='<div class="play"></div>';return t.replace("ID",e)+a}function lazyLoadYoutubeIframe(){var e=document.createElement("iframe"),t="ID?autoplay=1";t+=0===this.dataset.query.length?'':'&'+this.dataset.query;e.setAttribute("src",t.replace("ID",this.dataset.src)),e.setAttribute("frameborder","0"),e.setAttribute("allowfullscreen","1"),e.setAttribute("allow", "accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"),this.parentNode.replaceChild(e,this)}document.addEventListener("DOMContentLoaded",function(){var e,t,a=document.getElementsByClassName("rll-youtube-player");for(t=0;t<a.length;t++)e=document.createElement("div"),e.setAttribute("data-id",a[t].dataset.id),e.setAttribute("data-query", a[t].dataset.query),e.setAttribute("data-src", a[t].dataset.src),e.innerHTML=lazyLoadThumb(a[t].dataset.id),e.onclick=lazyLoadYoutubeIframe,a[t].appendChild(e)});</script>

<div class="modal">
	<div class="modal-content">
		<button class="close">X</button>
	</div>
</div>
<style type="text/css">
	#menu-gdpr-menu, .languages {padding-bottom:0px!important}

ul, ol {padding-bottom:20px!important}

@media screen and (max-width: 590px) {
  .two-columns-post {width:100%!important;}
}

#products-comparator .comparator-products > ul > li {width: 16rem;}

div.contact, div.locality {margin-top:1rem!important;}
li.zoomMap {margin-top:2rem!important;}
div.contact a {font-size: 1.2rem;}


.product-option {font-size: 15px; padding-top: 15px;color:#35a0b7;}

@media (min-width: 1200px) {
.section-html.section-products article { width:25%!important; } }


.inner-nav ul li:before, .parent-flex ul li:before, .wrapper-picto ul li:before, .list-pictures li:before, .parent-flex li:before, .comparator-labels ul li:before, .ul-list-link li:before, .ul-list-post li:before, .zoomMap:before, div.help ul li:before  {
display:none!important;
}


h3.list {
    text-align: center;
    margin-bottom: 1rem;
    width: 100%;
    color: #4ca928;
    font-size: 1.8rem!important;
    line-height: 2.4rem;
}
@media (max-width: 1440px){
h3.list {
    text-align: center;
    margin-bottom: 1rem;
    width: 100%;
    color: #4ca928;
    font-size: 1.8rem!important;
    line-height: 2.4rem;}
}

.ask-for-demo {padding: 4rem 6rem;} 
@media (max-width: 1160px) {.ask-for-demo {padding: 4rem 2rem;} 

.language-banner .wrapper {justify-content: normal;}

.language-banner .wrapper .text {margin-right:2rem;}

.widget_eff_newsletter #mc_embed_signup iframe {height:440px;}

@media (min-width: 1200px) {
section.wrapper-whitepaper iframe, section.whitepaper iframe {height:440px;}
.wrapper-whitepaper .whitepaper{padding:9rem 9rem 9rem 12rem}
}
section.footer-big .col-right #mc_embed_signup iframe {height:440px;}

#products-comparator .comparator-products header h2 {font-size: 2rem;font-weight: normal;}

@media screen and (max-width: 800px) {
  #tablewebinar tbody tr td{font-size:1rem!important;padding:0.5rem}
  #tablewebinar tbody tr td a.btn span {font-size:1rem!important;padding:0rem}
  #tablewebinar tbody tr td a.btn {padding:0.5rem 1rem!important;}
  .webinardescription {display:none;}
  #tablewebinar tbody tr th{font-size:1rem!important;padding:0.5rem}
}

@media screen and (max-width: 1070px) {
  .cities {width:20%!important;}
  .text-cities  {font-size:14px!important;}
  .third-city {width:20%!important;}
  .four-cities{padding-bottom:30rem;}
}
@media screen and (max-width: 800px) {
  .title {text-align:center;}
  .form-crm-day {height: 75rem!important;}
}
@media screen and (max-width: 590px) {
  .cities {width:35%!important;}
  .text-cities {font-size:14px!important;}
  .third-city {margin-left: 5rem!important;width:35%!important;}
  .four-cities{padding-bottom:65rem!important;}
}
@media screen and (max-width: 340px) {
  .cities {width:30%!important;}
  .text-cities {font-size:10px!important;}
  .third-city {margin-left: 5rem!important;width:30%!important;}

}

.speaker{text-align: left; width: 130px;border-width: 0px !important; color:#35a0b7;font-size: 16px;font-family: Arial;}
.conference {text-align: left;border-width: 0px !important;font-family: Arial;font-size: 16px;width: 60%;}
.time{border-width: 0px !important;text-align: left;}

li {
    list-style-position: inside;
}
p {
text-align:justify;
}

.whitepaper {
/*padding: 9rem 12rem;
margin-bottom: 28rem;
margin-left: 9rem;*/
width: 40%;
float: right;
}
.cards-download article {
    width: auto;
    float: left;
}</style>

</body>
</html>
