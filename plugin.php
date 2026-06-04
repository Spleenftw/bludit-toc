<?php
class PluginBluditToc extends Plugin {

    public function init() {
        $this->name        = 'Table of Contents';
        $this->description = 'Sticky ToC sidebar (desktop) and floating drawer (mobile) from article headings.';
        $this->dbFields    = array(
            'title'        => 'On this page',
            'navbarHeight' => 80,
            'maxLevel'     => 4,
        );
    }

    public function form() {
        global $L;

        $title        = htmlspecialchars($this->getValue('title'), ENT_QUOTES, 'UTF-8');
        $navbarHeight = (int) $this->getValue('navbarHeight');
        $maxLevel     = (int) $this->getValue('maxLevel');

        $h  = '';

        $h .= '<div class="form-group">';
        $h .= '<label>' . $L->get('Sidebar title') . '</label>';
        $h .= '<input class="form-control" type="text" name="title" value="' . $title . '">';
        $h .= '</div>';

        $h .= '<div class="form-group">';
        $h .= '<label>' . $L->get('Heading depth') . '</label>';
        $h .= '<select class="form-control" name="maxLevel">';
        $h .= '<option value="2"' . ($maxLevel === 2 ? ' selected' : '') . '>h2</option>';
        $h .= '<option value="3"' . ($maxLevel === 3 ? ' selected' : '') . '>h2, h3</option>';
        $h .= '<option value="4"' . ($maxLevel === 4 ? ' selected' : '') . '>h2, h3, h4</option>';
        $h .= '</select>';
        $h .= '<small class="form-text text-muted">' . $L->get('Deepest heading level included in the table of contents.') . '</small>';
        $h .= '</div>';

        $h .= '<div class="form-group">';
        $h .= '<label>' . $L->get('Navbar height (px)') . '</label>';
        $h .= '<input class="form-control" type="number" name="navbarHeight" min="0" max="300" value="' . $navbarHeight . '">';
        $h .= '<small class="form-text text-muted">' . $L->get('Pixel offset so heading anchors clear a fixed navbar.') . '</small>';
        $h .= '</div>';

        return $h;
    }

    public function siteHead() {
        return '<style>
.bltoc-sidebar{display:none;position:fixed;top:5rem;width:210px;max-height:calc(100vh - 6rem);overflow-y:auto;overflow-x:hidden;z-index:200;font-size:.8125rem;line-height:1.5;scrollbar-width:thin;--bltoc-text:#777;--bltoc-hover:#111;--bltoc-active:#0066cc;--bltoc-label:#999}
@media(prefers-color-scheme:dark){.bltoc-sidebar{--bltoc-text:#999;--bltoc-hover:#eee;--bltoc-active:#4d9fff;--bltoc-label:#666}}
@supports(color:var(--accent)){.bltoc-sidebar{--bltoc-active:var(--accent)}}
@supports(color:var(--text-muted)){.bltoc-sidebar{--bltoc-text:var(--text-muted)}}
.bltoc-header{margin-bottom:.65rem}
.bltoc-title{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--bltoc-label,#999)}
.bltoc-list{list-style:none;padding:0;margin:0}
.bltoc-item{padding:.12rem 0}
.bltoc-h3{padding-left:.75rem}
.bltoc-h4{padding-left:1.5rem}
.bltoc-sidebar a,.bltoc-drawer a{text-decoration:none!important;border:none!important;border-bottom:none!important;box-shadow:none!important;outline:none!important;background:none!important}
.bltoc-link{display:block;color:var(--bltoc-text,#777);line-height:1.5;transition:color .15s ease}
.bltoc-link:hover,.bltoc-link:focus,.bltoc-link:active{color:var(--bltoc-hover,#111)}
.bltoc-link.active{color:var(--bltoc-active,#0066cc);font-weight:600}
.bltoc-fab{display:none;position:fixed;left:1.5rem;bottom:1.5rem;z-index:1500;width:46px;height:46px;border-radius:50%;border:none;background:#0066cc;color:#fff;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 3px 10px rgba(0,0,0,.25);transition:transform .15s ease,box-shadow .15s ease}
.bltoc-fab:hover{transform:scale(1.08);box-shadow:0 4px 14px rgba(0,0,0,.3)}
.bltoc-fab svg{display:block}
.bltoc-drawer{position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.45);opacity:0;visibility:hidden;transition:opacity .22s ease,visibility 0s linear .22s}
.bltoc-drawer.is-open{opacity:1;visibility:visible;transition:opacity .22s ease}
.bltoc-drawer-panel{position:absolute;bottom:0;left:0;right:0;background:#fff;border-radius:1rem 1rem 0 0;padding:1.5rem 1.5rem calc(1.5rem + env(safe-area-inset-bottom,0px));max-height:72vh;overflow-y:auto;transform:translateY(100%);transition:transform .25s ease}
@media(prefers-color-scheme:dark){.bltoc-drawer-panel{background:#1e1e1e}}
@supports(color:var(--bg-primary)){.bltoc-drawer-panel{background:var(--bg-primary)}}
.bltoc-drawer.is-open .bltoc-drawer-panel{transform:translateY(0)}
.bltoc-drawer-title{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#999;margin-bottom:.75rem}
.bltoc-drawer .bltoc-list{list-style:none;padding:0;margin:0}
.bltoc-drawer .bltoc-link{display:block;padding:.5rem 0;color:#555;text-decoration:none}
.bltoc-drawer .bltoc-link:hover{color:#0066cc;text-decoration:none}
@media(prefers-color-scheme:dark){.bltoc-drawer .bltoc-link{color:#ccc}.bltoc-drawer .bltoc-link:hover{color:#4d9fff}}
@supports(color:var(--accent)){.bltoc-drawer .bltoc-link:hover{color:var(--accent)}}
</style>' . PHP_EOL;
    }

    public function siteBodyBegin() {
        $title = htmlspecialchars($this->getValue('title'), ENT_QUOTES, 'UTF-8');
        return '<aside class="bltoc-sidebar" id="bltoc-sidebar" aria-label="' . $title . '">'
             . '<div class="bltoc-header"><span class="bltoc-title">' . $title . '</span></div>'
             . '<nav id="bltoc-nav" aria-label="' . $title . '"></nav>'
             . '</aside>' . PHP_EOL;
    }

    public function siteBodyEnd() {
        $title        = json_encode($this->getValue('title'));
        $navbarHeight = (int) $this->getValue('navbarHeight');
        $maxLevel     = (int) $this->getValue('maxLevel');

        return '<script>(function(){' .
            'var TITLE=' . $title . ',' .
            'NAVBAR_HEIGHT=' . $navbarHeight . ',' .
            'MAX_LEVEL=' . $maxLevel . ';' .

            /* ---- Find content area ---- */
            'var content=document.querySelector(".content")||document.querySelector("article .entry-content")||document.querySelector(".entry-content")||document.querySelector(".post-content");' .
            'if(!content)return;' .

            /* ---- Collect headings (up to MAX_LEVEL) ---- */
            'var sel="h2";if(MAX_LEVEL>=3)sel+=",h3";if(MAX_LEVEL>=4)sel+=",h4";' .
            'var headings=content.querySelectorAll(sel);' .
            'if(!headings.length)return;' .

            /* ---- Assign IDs ---- */
            'var usedIds={};' .
            'Array.prototype.forEach.call(headings,function(h){' .
                'if(h.id){usedIds[h.id]=true;}' .
                'else{' .
                    'var base=h.textContent.trim().toLowerCase().replace(/[^a-z0-9\s-]/g,"").replace(/\s+/g,"-").replace(/^-+|-+$/g,"")||"heading";' .
                    'var id=base,n=2;while(usedIds[id]){id=base+"-"+(n++);}' .
                    'usedIds[id]=true;h.id=id;' .
                '}' .
                'h.style.scrollMarginTop=NAVBAR_HEIGHT+"px";' .
            '});' .

            /* ---- Build ToC list ---- */
            'var ul=document.createElement("ul");ul.className="bltoc-list";' .
            'Array.prototype.forEach.call(headings,function(h){' .
                'var li=document.createElement("li");li.className="bltoc-item bltoc-"+h.tagName.toLowerCase();' .
                'var a=document.createElement("a");a.href="#"+h.id;a.textContent=h.textContent;a.className="bltoc-link";' .
                'li.appendChild(a);ul.appendChild(li);' .
            '});' .

            /* ---- Populate nav ---- */
            'var nav=document.getElementById("bltoc-nav");' .
            'if(nav)nav.appendChild(ul);' .

            /* ---- Scroll-spy ---- */
            'var arr=Array.prototype.slice.call(headings);' .
            'function updateActive(){' .
                'var sy=window.scrollY||window.pageYOffset,thresh=sy+NAVBAR_HEIGHT+16,active=null;' .
                'arr.forEach(function(h){if(h.getBoundingClientRect().top+sy<=thresh)active=h;});' .
                'if(!nav)return;' .
                'Array.prototype.forEach.call(nav.querySelectorAll(".bltoc-link"),function(a){a.classList.remove("active");});' .
                'if(active){var lnk=nav.querySelector("a[href=\'#"+active.id+"\']");if(lnk)lnk.classList.add("active");}' .
            '}' .
            'window.addEventListener("scroll",updateActive,{passive:true});updateActive();' .

            /* ---- Mobile FAB + drawer ---- */
            'var fab=document.createElement("button");fab.type="button";fab.className="bltoc-fab";' .
            'fab.setAttribute("aria-label",TITLE);fab.setAttribute("aria-expanded","false");' .
            'fab.innerHTML=\'<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>\';' .
            'var drawer=document.createElement("div");drawer.className="bltoc-drawer";' .
            'drawer.setAttribute("role","dialog");drawer.setAttribute("aria-modal","true");drawer.setAttribute("aria-label",TITLE);' .
            'var panel=document.createElement("div");panel.className="bltoc-drawer-panel";' .
            'var dt=document.createElement("div");dt.className="bltoc-drawer-title";dt.textContent=TITLE;' .
            'var clone=ul.cloneNode(true);' .
            'panel.appendChild(dt);panel.appendChild(clone);drawer.appendChild(panel);' .
            'document.body.appendChild(fab);document.body.appendChild(drawer);' .

            'function openDrawer(){drawer.classList.add("is-open");document.body.style.overflow="hidden";fab.setAttribute("aria-expanded","true");}' .
            'function closeDrawer(){drawer.classList.remove("is-open");document.body.style.overflow="";fab.setAttribute("aria-expanded","false");}' .
            'fab.addEventListener("click",openDrawer);' .
            'drawer.addEventListener("click",function(e){if(e.target===drawer)closeDrawer();});' .
            'Array.prototype.forEach.call(clone.querySelectorAll("a"),function(a){a.addEventListener("click",closeDrawer);});' .
            'document.addEventListener("keydown",function(e){if((e.key==="Escape"||e.key==="Esc")&&drawer.classList.contains("is-open")){closeDrawer();fab.focus();}});' .

            /* ---- Dynamic positioning: place sidebar to the left of .content ---- */
            'var sb=document.getElementById("bltoc-sidebar");' .
            'var TOC_W=220,GAP=16;' .
            'function positionSidebar(){' .
                'if(!sb)return;' .
                'var rect=content.getBoundingClientRect();' .
                'if(rect.left>=TOC_W+GAP){' .
                    'sb.style.left=Math.max(8,rect.left-TOC_W-GAP)+"px";' .
                    'sb.style.display="block";' .
                    'fab.style.display="none";' .
                '}else{' .
                    'sb.style.display="none";' .
                    'fab.style.display="flex";' .
                '}' .
            '}' .
            'positionSidebar();' .
            'window.addEventListener("resize",positionSidebar);' .
        '})();</script>' . PHP_EOL;
    }
}
