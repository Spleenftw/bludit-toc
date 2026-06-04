<?php
class PluginBluditToc extends Plugin {

    public function init() {
        $this->dbFields = array(
            'title'        => 'On this page',
            'navbarHeight' => 80,
            'minWidth'     => 1280,
        );
    }

    public function form() {
        global $L;

        $title        = htmlspecialchars($this->getValue('title'), ENT_QUOTES, 'UTF-8');
        $navbarHeight = (int) $this->getValue('navbarHeight');
        $minWidth     = (int) $this->getValue('minWidth');

        $h  = '';

        $h .= '<div class="form-group">';
        $h .= '<label>' . $L->get('Sidebar title') . '</label>';
        $h .= '<input class="form-control" type="text" name="title" value="' . $title . '">';
        $h .= '</div>';

        $h .= '<div class="form-group">';
        $h .= '<label>' . $L->get('Navbar height (px)') . '</label>';
        $h .= '<input class="form-control" type="number" name="navbarHeight" min="0" max="300" value="' . $navbarHeight . '">';
        $h .= '<small class="form-text text-muted">' . $L->get('Pixel offset applied to heading anchors to clear a fixed navbar.') . '</small>';
        $h .= '</div>';

        $h .= '<div class="form-group">';
        $h .= '<label>' . $L->get('Sidebar min viewport width (px)') . '</label>';
        $h .= '<input class="form-control" type="number" name="minWidth" min="768" max="2560" value="' . $minWidth . '">';
        $h .= '<small class="form-text text-muted">' . $L->get('Below this width the mobile floating button is shown instead.') . '</small>';
        $h .= '</div>';

        return $h;
    }

    public function siteHead() {
        return '<style>
#bltoc-sidebar{--bltoc-text:#777;--bltoc-hover:#111;--bltoc-active:#0066cc;--bltoc-label:#999;--bltoc-fab-bg:#0066cc;--bltoc-fab-color:#fff}
@media(prefers-color-scheme:dark){#bltoc-sidebar{--bltoc-text:#999;--bltoc-hover:#eee;--bltoc-active:#4d9fff;--bltoc-label:#666}}
@supports(color:var(--accent)){#bltoc-sidebar{--bltoc-active:var(--accent)}}
@supports(color:var(--text-muted)){#bltoc-sidebar{--bltoc-text:var(--text-muted)}}
.bltoc-sidebar{display:none;position:fixed;left:1rem;top:5rem;width:210px;max-height:calc(100vh - 6rem);overflow-y:auto;overflow-x:hidden;z-index:200;font-size:.8125rem;line-height:1.5;scrollbar-width:thin}
.bltoc-header{margin-bottom:.65rem}
.bltoc-title{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--bltoc-label,#999)}
.bltoc-list{list-style:none;padding:0;margin:0}
.bltoc-item{padding:.12rem 0}
.bltoc-h3{padding-left:.75rem}
.bltoc-h4{padding-left:1.5rem}
.bltoc-link{display:block;color:var(--bltoc-text,#777);text-decoration:none;line-height:1.5;transition:color .15s ease}
.bltoc-link:hover{color:var(--bltoc-hover,#111);text-decoration:none}
.bltoc-link.active{color:var(--bltoc-active,#0066cc);font-weight:600}
.bltoc-fab{display:none;position:fixed;left:1.5rem;bottom:1.5rem;z-index:1500;width:46px;height:46px;border-radius:50%;border:none;background:var(--bltoc-fab-bg,#0066cc);color:var(--bltoc-fab-color,#fff);align-items:center;justify-content:center;cursor:pointer;box-shadow:0 3px 10px rgba(0,0,0,.25);transition:transform .15s ease,box-shadow .15s ease}
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
        $minWidth     = (int) $this->getValue('minWidth');

        return '<script>(function(){' .
            'var TITLE=' . $title . ',' .
            'NAVBAR_HEIGHT=' . $navbarHeight . ',' .
            'MIN_WIDTH=' . $minWidth . ';' .
            'var s=document.createElement("style");' .
            's.textContent="@media(min-width:"+MIN_WIDTH+"px){.bltoc-sidebar.bltoc-ready{display:block}}"' .
            '+"@media(max-width:"+(MIN_WIDTH-1)+"px){.bltoc-fab.bltoc-ready{display:flex}}";' .
            'document.head.appendChild(s);' .
            'var content=document.querySelector(".content")||document.querySelector("article .entry-content")||document.querySelector(".entry-content")||document.querySelector(".post-content");' .
            'if(!content)return;' .
            'var headings=content.querySelectorAll("h2,h3,h4");' .
            'if(!headings.length)return;' .
            'var usedIds={};' .
            'Array.prototype.forEach.call(headings,function(h){' .
                'if(h.id){usedIds[h.id]=true;}' .
                'else{' .
                    'var base=h.textContent.trim().toLowerCase().replace(/[^a-z0-9\s-]/g,"").replace(/\s+/g,"-").replace(/^-+|-+$/g,"")||"heading";' .
                    'var id=base,n=2;' .
                    'while(usedIds[id]){id=base+"-"+(n++);}' .
                    'usedIds[id]=true;h.id=id;' .
                '}' .
                'h.style.scrollMarginTop=NAVBAR_HEIGHT+"px";' .
            '});' .
            'var ul=document.createElement("ul");ul.className="bltoc-list";' .
            'Array.prototype.forEach.call(headings,function(h){' .
                'var li=document.createElement("li");li.className="bltoc-item bltoc-"+h.tagName.toLowerCase();' .
                'var a=document.createElement("a");a.href="#"+h.id;a.textContent=h.textContent;a.className="bltoc-link";' .
                'li.appendChild(a);ul.appendChild(li);' .
            '});' .
            'var nav=document.getElementById("bltoc-nav");' .
            'if(nav)nav.appendChild(ul);' .
            'var sb=document.getElementById("bltoc-sidebar");' .
            'if(sb)sb.classList.add("bltoc-ready");' .
            'var arr=Array.prototype.slice.call(headings);' .
            'function updateActive(){' .
                'var sy=window.scrollY||window.pageYOffset,thresh=sy+NAVBAR_HEIGHT+16,active=null;' .
                'arr.forEach(function(h){if(h.getBoundingClientRect().top+sy<=thresh)active=h;});' .
                'if(!nav)return;' .
                'Array.prototype.forEach.call(nav.querySelectorAll(".bltoc-link"),function(a){a.classList.remove("active");});' .
                'if(active){var lnk=nav.querySelector("a[href=\'#"+active.id+"\']");if(lnk)lnk.classList.add("active");}' .
            '}' .
            'window.addEventListener("scroll",updateActive,{passive:true});updateActive();' .
            'var fab=document.createElement("button");fab.type="button";fab.className="bltoc-fab bltoc-ready";' .
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
        '})();</script>' . PHP_EOL;
    }
}
