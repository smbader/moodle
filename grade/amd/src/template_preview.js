define([
    'core/modal_save_cancel',
    'core/modal_events',
    'core/templates',
    'core/str',
    'core_grades/repository'
], function(
    ModalSaveCancel,
    ModalEvents,
    Templates,
    Str,
    {fetchGradingTemplate}
) {

    class GradingTemplatePreview {
        constructor() {
            this.registerEventListeners();
        }

        registerEventListeners() {
            document.addEventListener('click', (event) => {
                if (event.target.classList.contains('template-preview')) {
                    const templateId = event.target.getAttribute('data-id');
                    const targetId = event.target.getAttribute('target-id');
                    const areaId = event.target.getAttribute('area-id');
                    const parentUrl = event.target.getAttribute('parent-url');
                    this.setUpModal(templateId, targetId, areaId, parentUrl);
                }
            });
        }

        async getBody(templateId, targetId, areaId, parentUrl) {
            const response = await fetchGradingTemplate(targetId, templateId, areaId, parentUrl);
            return Templates.render('core_grades/template_preview', response);
        }

        async setUpModal(templateId, targetId, areaId, parentUrl) {
            const modal = await ModalSaveCancel.create({
                title: 'Grading template preview',
                body: '',
                large: true,
            });
            const body = await this.getBody(templateId, targetId, areaId, parentUrl);
            modal.setBody(body);
            this.modal = modal;
            modal.show();
            Str.get_string('templatepick', 'core_grading')
                .then((btntext) => {
                    modal.setSaveButtonText(btntext);
                });
            modal.getRoot().on(ModalEvents.save, () => {
                window.parent.location.href = parentUrl + '&pick=' + templateId;
            });
        }
    }

    return {
        init: function() {
            return new GradingTemplatePreview();
        },
        rebindButtons: function() {
            GradingTemplatePreview.prototype.registerEventListeners();
        }
    };
});
