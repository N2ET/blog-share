import React from 'react';

import { Form, Input, Button, Checkbox, Radio,  Select, DatePicker, Spin } from 'antd';
import { ShareFormData, ShareProp } from '../interface';

const initialFormData : ShareFormData = {
    title: '',
    categories: [],
    tags: [],
    created: '',
    status: 'publish'
};

function Share (props : ShareProp) {

    const [form] = Form.useForm(props.form);

    const onValuesChange = (values : Partial<ShareFormData>) => {

    };

    return (
        <div className='share-box'>
            <Form
                initialValues={ initialFormData }
                onValuesChange={ onValuesChange }
                form={ form }>
                <Form.Item
                    label='标题'
                    name='title'>
                    <Input/>
                </Form.Item>

                <Form.Item
                    label='分类'
                    name='categories'>
                    <Select
                        mode='tags'
                        placeholder='选择/输入分类'>
                            
                    </Select>
                </Form.Item> 

                <Form.Item
                    label='标签'
                    name='tags'>
                    <Select
                        mode='tags'
                        placeholder='选择/输入标签'>

                    </Select>
                </Form.Item>

                {/* <Form.Item
                    label='创建时间'
                    name='created'>
                         <DatePicker showTime />
                    </Form.Item> */}

                <Form.Item
                    label='发布状态'
                    name='status'>
                    <Radio.Group>
                        <Radio value='publish'>发布</Radio>
                        <Radio value='waiting'>待审核</Radio>
                        <Radio value='private'>自己可见</Radio>
                    </Radio.Group>
                </Form.Item>

                <Form.Item>
                    <Button type='primary' htmlType='button' onClick={ function () {
                        if (props.onSubmit) {
                            props.onSubmit(
                                form.getFieldsValue() as ShareFormData
                            );
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