<?php

declare(strict_types=1);

use Mosaic\Fragment;

return new Fragment(<<<HTML
<script>
window.history.replaceState({url: document.location.href}, null, document.location.href);
window.addEventListener('popstate', event => {
    window.location.assign(event.state.url);
})

customElements.define('route-boundary', class extends HTMLElement {
    uri = this.getAttribute('uri');
    route = this.getAttribute('route');
    partial = this.getAttribute('partial');
     
    handleClick = event => {
         const link = event.target.matches('a') ? event.target : event.target.closest('a');
            if (link && (!link.target || link.target === '_self')) {
                const href = link.getAttribute('href');
                if (this.canFetch(href)) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.fetch(href, true);
                } 
            }
    }
    
    handleSubmit = event => {
        if (
            this.canFetch(event.target.action) 
            && (!event.target.target || event.target.target === '_self')
            && (!event.submitter.formTarget || event.submitter.formTarget === '_self')
        ) {
            event.preventDefault();
            event.stopPropagation();
            fetch(event.target.action, {
                method: event.target.method,
                body: new FormData(event.target, event.submitter),
                headers: {
                    Accept: "application/x.no-content"
                }
            }).then(response => {
                if (response.redirected) {
                    if (this.canFetch(response.url)) {
                        this.fetch(response.url, true);
                    } else {
                        window.location.assign(response.url);
                    }
                } else {
                    this.fetch(this.uri, false);
                }
            }).catch(error => {
                console.error(error);
            });   
        }
    }
    
    constructor() {
        super();
        this.addEventListener('click', this.handleClick);
        this.addEventListener('submit', this.handleSubmit);
    }

    connectedCallback() {
        if (this.hasAttribute('fetch-on-connected')) {
            this.fetch(this.uri, false, false);
        }
    }
    
    canFetch(url) {
        const currentURL = new URL(window.location.href);
        const urlObject = new URL(url, document.baseURI);
        return urlObject.host === currentURL.host && urlObject.pathname.startsWith(this.route);
    }
        
    fetch(url, history, fallback = true) {
          const urlObject = new URL(url, document.baseURI);
          urlObject.searchParams.set('_partial', this.partial);
          fetch(urlObject)
            .then(response => response.text())
            .then(htmlString => {
                const wrapper = document.createElement('div');
                wrapper.innerHTML = htmlString;
                this.replaceWith(...wrapper.childNodes);
                if (history) {
                    window.history.pushState({url: url}, null, url);
                }
            }).catch(error => {
                console.error(error);
                if (fallback) {
                    if (history) {
                        window.location.assign(url);
                    } else {
                        window.location.replace(url);
                    }
                }
            });
    }
})
</script>
HTML
);