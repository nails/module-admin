import services from './Services';
import API from './API';
import Modal from './Modal/Instance';

/* globals $, jQuery */
class Notes {
    /**
     * Construct Notes
     * @return {Notes}
     */
    constructor(adminController) {

        this.adminController = adminController;
        this.adminController.log('Constructing Notes');

        this.modal = (new Modal(this.adminController, {
            minWidth: '600px',
            maxWidth: '600px'
        }));

        this.confirmModal = (new Modal(this.adminController, {
            minWidth: '400px',
            maxWidth: '400px'
        }));

        this.adminController
            .onRefreshUi(() => {
                this.init();
            });

        return this;
    }

    // --------------------------------------------------------------------------

    /**
     * Initialise notes
     * @returns {Notes}
     */
    init() {

        this.adminController.log('Initialising Notes UI');

        let btns = document.querySelectorAll('.js-admin-notes:not(.js-admin-notes--processed)');
        btns
            .forEach(btn => {
                btn.classList.add('js-admin-notes--processed');

                let counter = document.createElement('span');
                counter.classList.add('admin-notes__counter');

                let modelName = btn.dataset.modelName;
                let modelProvider = btn.dataset.modelProvider;
                let itemId = btn.dataset.id;
                let title = btn.dataset.modalTitle || 'Notes';
                let showCount = btn.dataset.showCount;

                if (showCount) {
                    btn.append(counter);
                    this.setCounter(counter, '...');
                }

                btn
                    .addEventListener('click', (event) => {

                        event.preventDefault();
                        event.stopPropagation();

                        this.modal
                            .setTitle(title)
                            .show();

                        this.load(
                            modelName,
                            modelProvider,
                            itemId,
                            showCount,
                            counter
                        );
                    });
            });

        let itemsToCount = {};

        let items = document.querySelectorAll('.js-admin-notes:not(.js-admin-notes--count-processed[data-show-count="true"])');

        items.forEach(item => {

            item.classList.add('js-admin-notes--count-processed');

            let modelName = item.dataset.modelName;
            let modelProvider = item.dataset.modelProvider;
            let id = item.dataset.id;

            if (!Object.prototype.hasOwnProperty.call(itemsToCount, modelProvider)) {
                itemsToCount[modelProvider] = {};
            }

            if (!Object.prototype.hasOwnProperty.call(itemsToCount[modelProvider], modelName)) {
                itemsToCount[modelProvider][modelName] = [];
            }

            itemsToCount[modelProvider][modelName].push(id);

        });

        if (Object.entries(itemsToCount).length > 0) {
            this.countNotes(itemsToCount)
                .done((response) => {
                    for (const [provider, models] of Object.entries(response.data)) {
                        for (const [model, ids] of Object.entries(models)) {
                            for (const [id, count] of Object.entries(ids)) {
                                let containers = document.querySelectorAll(`.js-admin-notes.js-admin-notes--processed[data-model-name="${model}"][data-model-provider="${provider}"][data-id="${id}"]`);
                                containers.forEach((container) => {
                                    let counters = container.querySelectorAll('.admin-notes__counter');
                                    counters.forEach((counter) => {
                                        this.setCounter(counter, count);
                                    });
                                });
                            }
                        }
                    }
                });
        }

        return this;
    };

    // --------------------------------------------------------------------------

    /**
     * Load notes from the server
     * @param {String} modelName The model name
     * @param {String} modelProvider The model provider
     * @param {Number} itemId The item's ID
     * @param {Boolean} showCount Whether the button is showing a counter
     * @param {Element} counter The element which contains the counter
     */
    load(modelName, modelProvider, itemId, showCount, counter) {

        this.modal
            .setBody('Loading...')
            .setActions(null);

        this.loadNotes(modelName, modelProvider, itemId)
            .done((response) => {

                let ul = document.createElement('ul');
                ul.classList.add('admin-notes', 'list-unstyled');

                let liEmpty = document.createElement('li');
                liEmpty.classList.add('admin-notes__empty');
                liEmpty.innerText = 'No notes recorded for this item';

                ul.append(liEmpty);

                if (response.data.length) {
                    liEmpty.classList.add('hidden');
                    for (let i = 0, j = response.data.length; i < j; i++) {
                        ul.append(
                            this.renderMessageItem(
                                ul,
                                response.data[i].id,
                                response.data[i].message,
                                response.data[i].user,
                                response.data[i].date,
                                showCount,
                                counter
                            )
                        );
                    }
                }

                const renderLoadMore = (nextUrl) => {
                    const existing = ul.querySelector('.admin-notes__load-more');
                    if (existing) {
                        existing.remove();
                    }

                    if (!nextUrl) {
                        return;
                    }

                    const liLoadMore = document.createElement('li');
                    liLoadMore.classList.add('admin-notes__load-more');

                    const btnLoadMore = document.createElement('button');
                    btnLoadMore.classList.add('btn', 'btn-block', 'btn-default');
                    btnLoadMore.innerText = 'Load more';

                    btnLoadMore.addEventListener('click', () => {
                        btnLoadMore.disabled = true;
                        btnLoadMore.innerText = 'Loading...';

                        this.loadMoreNotes(nextUrl)
                            .done((response) => {
                                for (let i = 0, j = response.data.length; i < j; i++) {
                                    ul.insertBefore(
                                        this.renderMessageItem(
                                            ul,
                                            response.data[i].id,
                                            response.data[i].message,
                                            response.data[i].user,
                                            response.data[i].date,
                                            showCount,
                                            counter
                                        ),
                                        liLoadMore
                                    );
                                }
                                renderLoadMore(response.meta?.pagination?.next || null);
                            })
                            .always(() => {
                                if (btnLoadMore.isConnected) {
                                    btnLoadMore.disabled = false;
                                    btnLoadMore.innerText = 'Load more';
                                }
                            });
                    });

                    liLoadMore.append(btnLoadMore);
                    ul.append(liLoadMore);
                };

                renderLoadMore(response.meta?.pagination?.next || null);

                //  Build form
                let textarea = document.createElement('textarea');
                let btn = document.createElement('button');

                textarea.placeholder = 'Enter a note';
                textarea.style.width = '100%';
                textarea.style.height = '100px';

                textarea.addEventListener('keyup', () => {
                    btn.disabled = textarea.value.length <= 0;
                });

                btn.classList.add('btn', 'btn-block', 'btn-primary');
                btn.innerText = 'Add Note';
                btn.disabled = true;
                btn.addEventListener('click', () => {

                    textarea.disabled = true;
                    textarea.style.background = '#f9f9f9';
                    btn.innerText = 'Saving';
                    btn.disabled = true;

                    this.saveNote(modelName, modelProvider, itemId, textarea.value)
                        .done((response) => {
                            textarea.value = '';

                            let li = this.renderMessageItem(
                                ul,
                                response.data.id,
                                response.data.message,
                                response.data.user,
                                response.data.date,
                                showCount,
                                counter
                            );

                            ul.querySelector('.admin-notes__empty').classList.add('hidden');
                            ul.prepend(li);
                            li.classList.add('admin-notes__note--new');

                            if (showCount) {
                                this.setCounter(counter, ul.querySelectorAll('.admin-notes__note').length);
                            }

                            li.scrollIntoView({behavior: 'smooth'});
                        })
                        .always(() => {
                            textarea.disabled = false;
                            textarea.style.background = '#ffffff';
                            btn.innerText = 'Add Note';
                            btn.disabled = textarea.value.length <= 0;
                        });
                });

                this.modal
                    .setBody(ul)
                    .setActions([textarea, btn]);
            });
    }

    // --------------------------------------------------------------------------

    /**
     * Loads notes from the server
     * @param {object} dataBundle The data bundle
     * @return {jQuery.Deferred}
     */
    countNotes(dataBundle) {

        let $deferred = new $.Deferred();

        services
            .apiRequest({
                'url': API.notes.count,
                'method': 'POST',
                'data': {
                    'dataBundle': dataBundle,
                }
            })
            .then((response) => {
                $deferred.resolve(response.data);
            })
            .catch((error) => {
                this.showError(
                    'Failed to count notes',
                    error.response.data
                );
                $deferred.reject(error.response.data);
            });

        return $deferred.promise();
    }

    // --------------------------------------------------------------------------

    /**
     * Loads notes from the server
     * @param {String} modelName The model name
     * @param {String} modelProvider The model provider
     * @param {Number} itemId The ID of the item
     * @return {jQuery.Deferred}
     */
    loadNotes(modelName, modelProvider, itemId) {
        let $deferred = new $.Deferred();
        services
            .apiRequest({
                'url': API.notes.list,
                'data': {
                    'model_name': modelName,
                    'model_provider': modelProvider,
                    'item_id': itemId
                }
            })
            .then((response) => {
                $deferred.resolve(response.data);
            })
            .catch((error) => {
                this.showError(
                    'Failed to load notes',
                    error.response.data
                );
                $deferred.reject(error.response.data);
            });

        return $deferred.promise();
    }

    // --------------------------------------------------------------------------

    /**
     * Loads the next page of notes from a pagination URL
     * @param {String} nextUrl The absolute URL returned by meta.pagination.next
     * @return {jQuery.Deferred}
     */
    loadMoreNotes(nextUrl) {
        let $deferred = new $.Deferred();
        const url = new URL(nextUrl);
        const params = {};
        url.searchParams.forEach((value, key) => {
            params[key] = value;
        });

        services
            .apiRequest({url: API.notes.list, data: params})
            .then((response) => {
                $deferred.resolve(response.data);
            })
            .catch((error) => {
                this.showError('Failed to load notes', error.response.data);
                $deferred.reject(error.response.data);
            });

        return $deferred.promise();
    }

    // --------------------------------------------------------------------------

    /**
     * Save a new note to the server
     * @param {String} modelName The model name
     * @param {String} modelProvider The model provider
     * @param {Number} itemId The ID of the item
     * @param {String} message The message to save
     * @return {jQuery.Deferred}
     */
    saveNote(modelName, modelProvider, itemId, message) {
        let $deferred = new $.Deferred();
        services
            .apiRequest({
                'url': API.notes.create,
                'method': 'POST',
                'data': {
                    'model_name': modelName,
                    'model_provider': modelProvider,
                    'item_id': itemId,
                    'message': message
                }
            })
            .then((response) => {
                $deferred.resolve(response.data);
            })
            .catch((error) => {
                this.showError(
                    'Failed to save note',
                    error.response.data
                );
                $deferred.reject(error.response.data);
            });

        return $deferred.promise();
    }

    // --------------------------------------------------------------------------

    /**
     * Delete a particular note
     * @param {Number} id The ID of the note to delete
     * @return {jQuery.Deferred}
     */
    deleteNote(id) {
        let $deferred = new $.Deferred();

        services
            .apiRequest({
                'url': API.notes.delete(id),
                'method': 'DELETE'
            })
            .then((response) => {
                $deferred.resolve(response.data);
            })
            .catch((error) => {
                this.showError(
                    'Failed to delete note',
                    error.response.data
                );
                $deferred.reject(error.response.data);
            });

        return $deferred.promise();
    }

    // --------------------------------------------------------------------------

    /**
     * Compiles the message item
     * @param {Element} ul The containing list element
     * @param {Number} id The message ID
     * @param {String} message The message string
     * @param {Object} user The user object
     * @param {String} date The date string
     * @param {Boolean} showCount Whether the button is showing a counter
     * @param {Element} counter The element which contains the counter
     * @return {Element}
     */
    renderMessageItem(ul, id, message, user, date, showCount, counter) {

        let li = document.createElement('li');
        li.classList.add('admin-notes__note');

        let divMessage = document.createElement('div');
        divMessage.classList.add('admin-notes__note__message');
        divMessage.innerHTML = message;

        let buttonDelete = document.createElement('button');
        buttonDelete.classList.add('admin-notes__note__delete');
        buttonDelete.innerHTML = '&times;';

        let divMeta = document.createElement('div');
        divMeta.classList.add('admin-notes__note__meta');

        let spanUser = document.createElement('span');
        spanUser.classList.add('admin-notes__note__meta__user');
        spanUser.innerHTML = user.id ? `${user.first_name} ${user.last_name}` : 'Unknown User';

        let spanDate = document.createElement('span');
        spanDate.classList.add('admin-notes__note__meta__date');
        spanDate.innerHTML = date;


        buttonDelete
            .addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                this.confirmModal
                    .setTitle('Delete Note')
                    .setBody('Are you sure you want to delete this note?')
                    .clearActions()
                    .addAction('Cancel', ['btn-default'], () => {
                        this.confirmModal.hide();
                    })
                    .addAction('Delete', ['btn-danger'], () => {
                        this.confirmModal.hide();
                        this.deleteNote(id)
                            .done(() => {
                                li.classList.add('admin-notes__note--removing');
                                li.addEventListener('animationend', () => {
                                    li.remove();
                                    let count = ul.querySelectorAll('.admin-notes__note').length;
                                    if (showCount) {
                                        this.setCounter(counter, count);
                                    }
                                    if (count === 0) {
                                        ul.querySelector('.admin-notes__empty').classList.remove('hidden');
                                    }
                                }, {once: true});
                            });
                    });

                this.confirmModal.actionButtons[0].style.marginRight = '0.5rem';
                this.confirmModal.show();
            });

        divMeta.append(
            spanUser,
            spanDate
        );

        li.append(
            divMessage,
            buttonDelete,
            divMeta
        );

        return li;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the counter to a specific value
     * @param {Element} element The DOMElement
     * @param {Number|String} count The value
     * @return {Notes}
     */
    setCounter(element, count) {
        element.innerHTML = count || '';
        return this;
    }

    // --------------------------------------------------------------------------
    /**
     * Renders an error
     * @param {String} title The title to give the modal
     * @param {Object} data The response text from the server
     */
    showError(title, data) {

        this.adminController.error(data.error);

        let message = data.error || data.message || 'An unknown error occurred';

        let alert = document.createElement('div');
        alert.classList.add('alert', 'alert-danger');
        alert.innerHTML = `<p><strong>${title}</strong></p><p>${message}</p>`;

        this.modal.setBody(alert);

        return this;
    }
}

export default Notes;
