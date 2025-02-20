import {call as fetchMany} from 'core/ajax';
export const fetchGradingTemplate = (
    targetid,
    templateid,
    areaid,
    parenturl
) => fetchMany([{
    methodname: 'core_grading_get_grading_template_preview',
    args: {
        targetid,
        templateid,
        areaid,
        parenturl
    }
}])[0];
