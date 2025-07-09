import services from './Services'
import API from "./API";
import Modal from './Modal/Instance';

class DataExport {
    constructor(adminController) {
        this.adminController = adminController;
        this.adminController.log('Constructing')
        this.dom = {
            form: document.getElementById('export-form'),
            recent: document.getElementById('export-recent'),
            recentContainer: document.getElementById('export-recent-container'),
        }
        this.modal = (new Modal(this.adminController));
        this.recent = [];
        this.bindForm()
        this.loadRecent();
    }

    bindForm() {
        this.dom.form
            .addEventListener('submit', (event) => {
                event.preventDefault();
                event.stopPropagation();
                this.generate()
            });
    }

    generate() {
        this.adminController.log('Generate');

        let formData = new FormData(this.dom.form);
        let requestJson = {
            source: formData.get('source'),
            format: formData.get('format'),
            options: {},
        };

        let prefix = 'options';
        for (const [key, value] of formData.entries()) {
            if (key.startsWith(prefix) && key !== prefix) {

                // Extract the nested key (everything after the prefix)
                const nestedKey = key.substring(prefix.length);
                // Remove the first '[' and replace all remaining '[' with '.' for easier access
                const cleanKey = nestedKey.substring(1).replace(/\]\[/g, '.').replace(/\]/g, '');
                //  We're only interested in options which match the selected source
                if (requestJson.source) {
                    if (cleanKey.startsWith(requestJson.source)) {
                        let cleanerKey = cleanKey.substring(requestJson.source.length + 1);
                        requestJson.options[cleanerKey] = value;
                    }
                }
            }
        }

        let errors = [];
        if (!requestJson.source) {
            errors.push('An export source must be selected.');
        }

        if (!requestJson.format) {
            errors.push('An export format must be selected.');
        }

        if (errors.length > 0) {

            let p = document.createElement('p');
            p.innerText = 'The following errors must be corrected:';

            let ul = document.createElement('ul');
            for (let i = 0; i < errors.length; i++) {
                let li = document.createElement('li');
                li.innerText = errors[i];
                ul.appendChild(li);
            }

            this.modal
                .setTitle('There are errors')
                .setBody([p, ul, this.modalCloseBtn()])
                .show();
            return;
        }

        services
            .apiRequest({
                method: 'POST',
                url: API.export.create,
                data: {
                    source: requestJson.source,
                    format: requestJson.format,
                    options: JSON.stringify(requestJson.options),
                }
            })
            .then(() => {
                this.loadRecent('PREPEND');

                let p = document.createElement('p');
                p.innerText = 'Your export has been requested successfully and will update in the list below when it is ready.';

                this.modal
                    .setTitle('Export requested')
                    .setBody([p, this.modalCloseBtn()])
                    .show();

            })
            .catch(error => {

                let lines = [];

                let p = document.createElement('p');
                p.innerText = error.response.data.error;
                lines.push(p);

                for (const [key, value] of Object.entries(error.response.data.details)) {
                    let p = document.createElement('p');
                    p.innerText = `${key}: ${value}`;
                    lines.push(p);
                }

                lines.push(this.modalCloseBtn());

                this.modal
                    .setTitle('There were errors')
                    .setBody(lines)
                    .show();
            });
    }

    loadRecent(newItemBehaviour) {
        this.adminController.log('Loading recent exports');

        clearTimeout(this.loadRecentTimeout);

        services
            .apiRequest({
                url: API.export.list
            })
            .then((response) => {

                let exports = response.data.data || [];

                this.renderRecent(exports, newItemBehaviour);

                /**
                 * Poll regularly if there are reports that might change state soon
                 * If everything is unlikely to change, then refresh every minute to
                 * remove reports which might get cleaned.
                 */

                let timeout = exports.length && exports.find(x => x.status === 'PENDING' || x.status === 'RUNNING')
                    ? 2500
                    : 60000

                this.loadRecentTimeout = setTimeout(() => {
                    this.loadRecent();
                }, timeout);
            });
    }

    renderRecent(exports, newItemBehaviour) {
        this.adminController.log('Rendering recent exports', exports);

        // Track which IDs exist in the current response
        const responseIds = exports.map(item => item.id);

        // Update existing items or add new ones
        for (let i = 0; i < exports.length; i++) {
            let recent = this.recent.find(x => x.id === exports[i].id);
            if (recent) {
                this.updateExisting(recent.dom, exports[i]);
            } else {
                this.addNew(exports[i], newItemBehaviour);
            }
        }

        // Remove items that are no longer in the response
        // Use a reverse loop to safely remove items while iterating
        for (let i = this.recent.length - 1; i >= 0; i--) {
            if (!responseIds.includes(this.recent[i].id)) {
                // Remove the DOM element
                if (this.recent[i].dom && this.recent[i].dom.parentNode) {
                    this.recent[i].dom.parentNode.removeChild(this.recent[i].dom);
                }
                // Remove from the recent array
                this.recent.splice(i, 1);
            }
        }

        //  Show/hide the container
        if (this.recent.length) {
            this.dom.recentContainer.classList.remove('hidden');
        } else {
            this.dom.recentContainer.classList.add('hidden');
        }
    }

    updateExisting(dom, item) {
        this.adminController.log('Updating existing export item', dom, item);

        let statusCell = dom.querySelector('td.export-status');
        statusCell.className = this.getStatusCellClasses(item).join(' ');
        statusCell.innerHTML = this.getStatusCellHtml(item);

        let generatedCell = dom.querySelector('td.export-generated');
        generatedCell.innerHTML = this.getGeneratedCellHtml(item)

        let actionsCell = dom.querySelector('td.export-actions');
        actionsCell.innerHTML = this.getActionsCellHtml(item);
    }

    addNew(item, newItemBehaviour) {
        this.adminController.log('Adding new export item', item);

        let tr = document.createElement('tr');
        tr.appendChild(this.createCell({
            'class': 'export-source',
            'value': item.source?.label
        }));
        tr.appendChild(this.createCell({
            'class': 'export-options',
            'value': `<pre>${JSON.stringify(item.options, null, 2)}</pre>`
        }));
        tr.appendChild(this.createCell({
            'class': 'export-format',
            'value': item.format?.label
        }));
        tr.appendChild(this.createCell({
            'class': this.getStatusCellClasses(item),
            'value': this.getStatusCellHtml(item)
        }));
        tr.appendChild(this.createCell({
            'class': 'export-requested',
            'value': item.created.formatted
        }));
        tr.appendChild(this.createCell({
            'class': 'export-generated',
            'value': this.getGeneratedCellHtml(item)
        }));
        tr.appendChild(this.createCell({
            'class': ['export-actions', 'actions'],
            'value': this.getActionsCellHtml(item)
        }));

        item.dom = tr;
        this.recent.push(item);

        if (newItemBehaviour === 'PREPEND') {
            this.dom.recent.prepend(tr);
        } else {
            this.dom.recent.append(tr);
        }
    }

    getStatusCellClasses(item) {
        let classes = ['export-status'];

        if (item.status === 'COMPLETE') {
            classes.push('success');

        } else if (item.status === 'FAILED') {
            classes.push('danger');

        } else {
            classes.push('warning');
        }

        return classes;
    }

    getStatusCellHtml(item) {
        return item.status === 'FAILED' && item.error
            ? `${item.status}<small>${item.error}</small>`
            : item.status;
    }

    getGeneratedCellHtml(item) {
        return item.status === 'COMPLETE'
            ? item.modified.formatted
            : '<span class="text-muted">&mdash;</span>';
    }

    getActionsCellHtml(item) {
        return item.download?.url
            ? `<a href="${item.download.url}" class="btn btn-primary btn-sm">Download</a>`
            : '';
    }

    createCell(options) {
        let cell = document.createElement('td');

        if (options.class) {
            if (Array.isArray(options.class)) {
                cell.classList.add(...options.class);
            } else {
                cell.classList.add(options.class);
            }
        }

        if (options.value) {
            cell.innerHTML = options.value;
        }

        return cell;
    }

    modalCloseBtn() {
        let btn = document.createElement('button');
        btn.innerText = 'Close';
        btn.style.marginRight = '0.5em';
        btn.classList.add('btn', 'btn-primary', 'btn-block');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            this.modal.hide();
        });

        let p = document.createElement('p');
        p.appendChild(btn);

        return p;
    }
}

export default DataExport
