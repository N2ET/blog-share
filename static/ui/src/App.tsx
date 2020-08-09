import React, { useState } from 'react';
import './App.css';
import { Row, Col } from 'antd';
import Share from './share/Share';

interface AppState {
    url: string
}

function App() {

    let initData = (window as any).blogShareData || {};

    let [state, setState] = useState({
        url: initData.url
    });

    (window as any).setShareData = (data: AppState) => {
        setState({
            url: data.url
        });
    };

    const onSubmit = () => {

    };

    return (
        <div className="share">
            <Row>
                <Col span={2}></Col>
                <Col span={20}>
                    <Share onSubmit={ onSubmit }/>
                </Col>
                <Col span={2}></Col>
            </Row>

        </div>
    );
}

export default App;
