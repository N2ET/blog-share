import { FormInstance } from "antd/es/form";

export interface ShareFormData {
    title: string,
    categories: string[],
    tags: string[],
    created: string,
    status: string
}

export interface ShareProp {
    onSubmit?: (data: ShareFormData) => any
    postData?: ShareFormData,
    form?: FormInstance
}