window.history.replaceState({url: document.location.href}, null, document.location.href);
window.addEventListener('popstate', event => {
    window.location.assign(event.state.url);
})
if (!customElements.get('route-boundary')) {
    customElements.define('route-boundary', class extends HTMLElement {
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
            if (this.canFetch(event.target.action) && (!event.target.target || event.target.target === '_self') && (!event.submitter.formTarget || event.submitter.formTarget === '_self')) {
                event.preventDefault();
                event.stopPropagation();
                fetch(event.target.action, {
                    method: event.target.method, body: new FormData(event.target, event.submitter), headers: {
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
                        this.fetch(window.location.href, false);
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
                this.fetch(window.location.href, false, false);
            }
        }

        removeLastPathSegment(path) {
            if (path === '/') {
                return '/';
            }
            const segments = path.split('/')
            segments.pop()
            const newPath = segments.join('/')
            if (!newPath.length) {
                return '/'
            }
            return newPath
        }

        canFetch(url) {
            const currentURL = new URL(window.location.href);
            const requestedURL = new URL(url, document.baseURI);
            const requestedBase = this.removeLastPathSegment(requestedURL.pathname)
            return requestedURL.host === currentURL.host && requestedBase.startsWith(this.getAttribute('partial'));
        }

        loadStyle(href) {
            if (!document.querySelector(`link[href='\${href}']`)) {
                const style = document.createElement('link');
                style.rel = 'stylesheet';
                style.href = href;
                document.head.appendChild(style);
            }
        }

        loadScript(src) {
            if (!document.querySelector(`script[src='\${src}']`)) {
                const script = document.createElement('script');
                script.src = src;
                document.head.appendChild(script)
            }
        }

        fetch(url, history, fallback = true) {
            const requestedURL = new URL(url, document.baseURI);
            requestedURL.searchParams.set('_partial', this.getAttribute('partial'));
            fetch(requestedURL)
                .then(response => response.text())
                .then(htmlString => {
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = htmlString;
                    const template = wrapper.querySelector('template')
                    if (wrapper.querySelector('script')) {
                        const data = JSON.parse(wrapper.querySelector('script').innerText)
                        document.head.querySelectorAll('script[src]').forEach(script => {
                            if (!script.src.endsWith('.client-router.js')) {
                                script.remove()
                            }
                        });
                        if (Array.isArray(data.scripts)) {
                            data.scripts.forEach(this.loadScript)
                        }
                        document.head.querySelectorAll('link[rel=stylesheet]').forEach(style => style.remove())
                        if (Array.isArray(data.styles)) {
                            data.styles.forEach(this.loadStyle)
                        }
                    }
                    document.title = template.dataset.title
                    this.replaceWith(template.content);
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
}