/* export QuickAction */

import API from './API'
import services from './Services'

class QuickAction {

    /**
     * Construct QuickAction
     * @return {QuickAction}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.adminController
            .onRefreshUi((e, domElement) => {
                if (!this.instantiated) {
                    this.instantiated = true;
                    this
                        .buildUI()
                        .bindEvents();
                }
            });
    }

    buildUI() {

        this.container = document.createElement('div');
        this.container.classList.add('admin-quick-action');

        this.inner = document.createElement('div');
        this.inner.classList.add('admin-quick-action__inner');

        this.input = document.createElement('input');
        this.input.placeholder = 'Search for anything...';
        this.input.classList.add('admin-quick-action__input');
        this.input.autocomplete = 'off';
        this.input.autocapitalize = 'off';
        this.input.spellcheck = 'false';

        this.results = document.createElement('ul');
        this.results.classList.add('admin-quick-action__results');

        this.inner.append(this.input);
        this.inner.append(this.results);
        this.container.append(this.inner);

        document.body.append(this.container);

        return this;
    }

    bindEvents() {

        //  Open on cmd/ctr + k
        //  Close if the escape key is hit
        window.addEventListener('keypress', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                this.show();

            } else if (e.key === 'Escape') {
                this.hide();
            }
        });

        //  Bind to arrows for result navigation
        window.addEventListener('keydown', (e) => {
            if (this.isShown()) {
                if (e.key === 'ArrowUp') {
                    this.focusPreviousResult();
                } else if (e.key === 'ArrowDown') {
                    this.focusNextResult();
                }
            }
        });

        //  Close if the background is clicked
        this.container.addEventListener('click', () => {
            this.hide();
        });

        //  Stop clicks on the modal causing the view to close
        this.inner.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        this.input.addEventListener('keyup', () => {
            this.search(this.input.value);
        });

        return this;
    }

    show() {
        if (!this.isShown()) {
            this.input.value = '';
            this.results.innerHTML = '';
            this.container.classList.add('show');
            document.body.classList.add('noScroll');
            setTimeout(() => {
                this.input.focus();
            }, 250);
        }

        return this;
    }

    hide() {
        if (this.isShown()) {
            this.container.classList.remove('show');
            document.body.classList.remove('noScroll');
        }

        return this;
    }

    isShown() {
        return this.container.classList.contains('show');
    }

    search(query) {
        clearTimeout(this.debounce);
        this.debounce = setTimeout(async () => {
            if (query !== this.lastSearch) {
                this.lastSearch = query;
                if (query.length) {

                    this.adminController.log(`Searching; query: ${query}`);

                    this.input.classList.add('searching');

                    let res = await services.apiRequest({
                        url: API.quickAction(
                            encodeURIComponent(query),
                            encodeURIComponent(window.location.href)
                        ),
                    });

                    this.clear();
                    res.data.data.map((item) => {
                        this.addResult(item);
                    });
                    this.focusFirstResult();

                } else {
                    this.clear();
                }
            }
        }, 500);
    }

    addResult(item) {
        let li = document.createElement('li');
        let a = document.createElement('a');

        a.href = item.url;
        a.innerHTML = item.label;

        if (item.sublabel) {
            a.innerHTML += `<small>${item.sublabel}</small>`;
        }

        li.append(a);

        a.addEventListener('mouseover', () => {
            a.focus();
        });

        this.results.append(li);
    }

    focusPreviousResult() {
        let current = this.results.querySelector('a:focus');
        if (current) {
            let next = current.closest('li').previousSibling;
            if (next) {
                next.querySelector('a').focus();
            } else {
                this.input.focus();
            }
        }
    }

    focusNextResult() {
        let current = this.results.querySelector('a:focus');
        if (current) {
            let next = current.closest('li').nextSibling;
            if (next) {
                next.querySelector('a').focus();
            }
        }
    }

    focusFirstResult() {

        this.adminController.log('Focusing first result');
        let results = this.results.querySelectorAll('li');
        if (results.length) {
            results[0].querySelector('a').focus();
        }
    }

    focusLastResult() {

        this.adminController.log('Focusing last result');
        let results = this.results.querySelectorAll('li');
        if (results.length) {
            results[results.length - 1].querySelector('a').focus();
        }
    }

    clear(clearInput) {
        this.adminController.log('Resetting UI');
        this.results.innerHTML = '';
        this.input.classList.remove('searching');
        return this;
    }
}

export default QuickAction;
