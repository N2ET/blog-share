import React, { useReducer } from 'react';
// import update from 'immutability-helper';

import { Form, Input, Button, Checkbox, Radio,  Select, DatePicker, Spin } from 'antd';


interface ShareFormData {
    title: string,
    categories: string[],
    tags: string[],
    created: string,
    status: string
}

const initialFormData : ShareFormData = {
    title: 'titlexx',
    categories: [],
    tags: [],
    created: '',
    status: 'publish'
};

const reducer = (state : ShareFormData, action : { type: string, data: any }) => {
    const { type, data } = action;

    switch (type) {


        default:
            return Object.assign({}, state, {
                [type]: data
            });
            // return update(state, {
            //     [type]: data
            // });
    }

};

interface ShareProp {
    onSubmit?: (data: ShareFormData) => any
}

function Share (props : ShareProp) {

    const [state, dispatch] = useReducer(reducer, initialFormData);

    return (
        <div className="share-box">
            <Form
                initialValues={ state }>
                <Form.Item
                    label="标题"
                    name="title">
                    <Input onChange={ e => {
                            dispatch({
                                type: 'title',
                                data: e.target.value
                            });
                        } } />
                </Form.Item>

                <Form.Item
                    label="分类"
                    name="categories">
                    <Select
                        mode="tags"
                        placeholder="选择/输入分类"
                        onChange={ value => {
                            dispatch({
                                type: 'categories',
                                data: value
                            });
                        } }>
                            
                        </Select>
                </Form.Item> 

                <Form.Item
                    label="标签"
                    name="tags">
                    <Select
                        mode="tags"
                        placeholder="选择/输入标签"
                        onChange={ value => {
                            dispatch({
                                type: 'categories',
                                data: value
                            });
                        } }> 

                        </Select>
                </Form.Item>

                {/* <Form.Item
                    label="创建时间"
                    name="created">
                         <DatePicker showTime />
                    </Form.Item> */}

                <Form.Item
                    label="发布状态"
                    name="status">
                    <Radio.Group 
                        onChange={ value => {
                            dispatch({
                                type: 'status',
                                data: value
                            });
                        } }>
                        <Radio value="publish">发布</Radio>
                        <Radio value="waiting">审核</Radio>
                    </Radio.Group>
                </Form.Item>

                <Form.Item>
                    <Button type="primary" htmlType="button" onClick={ function () {
                        if (props.onSubmit) {
                            props.onSubmit(state);
                        }
                    } }>
                        分享
                    </Button>
                </Form.Item>
                    
            </Form>
        </div>
    );
}

export default Share;