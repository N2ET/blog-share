import React, { useState } from 'react';
import {FormInstance} from "antd/es/form";
import { Row, Col, Spin, Alert, Card, Form } from 'antd';
import axios from 'axios';
import Share from './share/Share';
import { ShareFormData } from "./interface";
import './App.css';


interface AppState {
    url: string,
    serverUrl: string,
    postData?: ShareFormData
}

function App() {

    let initData = (window as any).blogShareData || {};

    let [state, setState] = useState({
        url: initData.url,
        serverUrl: initData.serverUrl,
        postData: initData.postData
    });
    let [loading, setLoading] = useState(false);
    let [message, setMessage] = useState(null as (null | string));

    const [form] : FormInstance[] = Form.useForm();

    const appWindow = window as any;
    appWindow.setSharePostData = (data : AppState) => {
        setState(Object.assign({}, state, {
            url: data.url,
            postData: data.postData || {}
        }));

        form.setFieldsValue(data.postData || {});

        setMessage(null);
        setLoading(false);
    };

    appWindow.setServerUrl = (url : string) => {
        setState(Object.assign({}, state, {
            serverUrl: url
        }));
    };

    const onSubmit = (postData : ShareFormData) => {

        setLoading(true);

        // 此处发送请求
        axios.post(state.serverUrl, {
            postUrl: state.url,
            data: postData
        })
            .then(ret => {
                let data = ret.data;

                if (data) {
                    if (data.success) {
                        setMessage('');
                    } else {
                        setMessage(data.message);
                    }
                }

                if (appWindow.onShareSubmit) {
                    appWindow.onShareSubmit(data);
                }
            })
            .catch(reason => {
                setMessage(reason.message);
                if (appWindow.onShareSubmit) {
                    appWindow.onShareSubmit({
                        success: 0,
                        message: reason.message
                    });
                }
            })
            .finally(() => {
                setLoading(false);
            });

    };

    const onClose = () => {
        if (appWindow.onShareCancel) {
            appWindow.onShareCancel();
        }
    };



    return (
        <Card bordered={ false } title={ '分享到Typecho' } extra={ <a href={'javascript:void(0);'} onClick={ onClose }>Close</a> }>
            <Spin spinning={ loading }>
                <Row>
                    <Col span={ 1 } />
                    <Col span={ 22 }>
                        {
                            message ? (<Alert type={ 'error' } message={ message } showIcon={ true } />) : []
                        }
                        {
                            !message && message !== null && (<Alert type={ 'success' } message={ '分享成功' } showIcon={ true } />)
                        }
                    </Col>
                    <Col span={ 1 } />
                </Row>

                <Row>
                    <Col span={ 1 } />
                    <Col span={ 22 }>
                        <Share onSubmit={ onSubmit } form={ form }/>
                    </Col>
                    <Col span={ 1 } />
                </Row>

            </Spin>

        </Card>
    );
}

export default App;
