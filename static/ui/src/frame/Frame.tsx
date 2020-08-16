/**
 * @file frame框架
 */

import React, {useState, useRef, useEffect} from 'react';
import ReactDOM from 'react-dom';
import { ShareFormData } from "../interface";

import './Frame.css';

interface FrameData {
    url: string,
    postUrl: string,
    serverUrl: string,
    postData?: ShareFormData
}

interface ShareFrameProp {
    onSetShareData?: () => {
        url?: string,
        data?: Partial<ShareFormData>
    },

    serverUrl?: string,

    settingPageUrl?: string
}

interface ShareWindowProps {
    onSubmit?: () => void,
    onCancel?: () => void
    hidden: boolean,

    serverUrl?: ShareFrameProp['serverUrl'],
    settingPageUrl?: ShareFrameProp['settingPageUrl']
}

const initData : FrameData = {
    url: '',
    serverUrl: '',
    postUrl: ''
};

const task = (function () {
    let resolveTask : (value? : unknown) => void;
    let rejectTask : (value ? : unknown) => void;
    const promise = new Promise((resolve, reject) => {
        resolveTask = resolve;
        rejectTask = reject;
    });

    return {
        loadTask: promise,
        // @ts-ignore
        resolveTask,
        // @ts-ignore
        rejectTask
    }
}())

function ShareWindow (props : ShareWindowProps) {

    // 初始数据
    let shareData = (window as any).blogShareData || {};

    let [height, setHeight] = useState(300);
    let [state, setState] = useState(Object.assign({}, initData, shareData));
    let frameRef = useRef<HTMLIFrameElement>(null);

    // 如果此处不使用 userRef和useEffect，后面的setSharePostData等的loadTask.then的回调中，读取到的state是旧的，
    // 从stateRef读取current才是其他地方调用setState之后的最新的值
    // 下面的useEffect如果第2个参数设置[state]，则后续从stateRef.current读取的值依然还是旧的
    let stateRef = useRef(state);
    useEffect(() => {
        stateRef.current = state;
    });

    const onSubmit = () => {

        setHeight(getWindow().document.body.scrollHeight);

        if (props.onSubmit) {
            props.onSubmit();
        }
    };

    const onCancel = () => {
        if (props.onCancel) {
            props.onCancel();
        }
    };

    const getWindow = () => {
        return (frameRef.current?.contentWindow as any);
    };

    // 更好的方式？task是在外部创建的
    const [{
        loadTask,
        resolveTask
    }] = useState(task);

    const onLoad = () => {
        const win = getWindow()
        win.onShareSubmit = onSubmit;
        win.onShareCancel = onCancel;
        setHeight(win.document.body.scrollHeight);

        resolveTask();
    };

    const setSharePostData = (postUrl: string, data?: ShareFormData) => {

        loadTask.then(() => {

            // 读取最新值，如果不从stateRef读取，直接使用state则读取到的是旧的值
            let state = stateRef.current;

            setState(
                Object.assign({}, state, {
                    postUrl: postUrl,
                    postData: data
                })
            );

            getWindow().setSharePostData({
                url: postUrl,
                postData: data
            });
        });
    };

    const setServerUrl = (url: string) => {

        loadTask.then(() => {

            let state = stateRef.current;

            setState(Object.assign({}, state, {
                serverUrl: url
            }));

            getWindow().setServerUrl(url);
        });

    };

    const setSettingPageUrl = (url: string) => {

        let state = stateRef.current;

        setState(Object.assign({}, state, {
            url: url
        }));
    };

    Object.assign((window as any).BlogShare, {

        setSharePostData,
        setServerUrl,
        setSettingPageUrl
    });

    useEffect(() => {
        // 此处加props.hidden会导致第二次点分享，高度不对
        // if (props.hidden) {
            setHeight(getWindow().document.body.scrollHeight);
        // }

    }, [props.hidden]);

    useEffect(() => {
        if (props.settingPageUrl) {
            setSettingPageUrl(props.settingPageUrl);
        }
    }, [props.settingPageUrl]);

    useEffect(() => {
        if (props.serverUrl) {
            setServerUrl(props.serverUrl);
        }
    }, [props.serverUrl]);

    return (
        <div className='blog-share_container' style={ {
            display: props.hidden ? 'none' : 'block'
        } }>
            <iframe className='blog-share_frame' src={ state.url }
                    ref={frameRef}
                    onLoad={ onLoad }
                    style={ {
                        height: height + 'px'
                    } }>
            </iframe>
        </div>
    );
}


function ShareFrame (props : ShareFrameProp) {

    let [hidden, setHidden] = useState(true);
    const onSubmit = () => {

        setHidden(true);
    };

    const onCancel = () => {
        setHidden(true);
    };

    return (<>
        <button className={ 'share-blog-btn' } onClick={
            () => {

                setHidden(false);

                if (props.onSetShareData) {
                    let data = props.onSetShareData();
                    if (data) {
                        ((window as any).BlogShare).setSharePostData(data.url, data.data);
                    }
                }
            }
        }>
            分享到Typecho
        </button>
        {
            ReactDOM.createPortal(
                <ShareWindow
                    hidden={ hidden }
                    onSubmit={ onSubmit }
                    onCancel={ onCancel }
                    settingPageUrl={ props.settingPageUrl }
                    serverUrl={ props.serverUrl }
                />,
                window.document.body
            )
        }
    </>);
}

type initProp = ShareFrameProp & {
    dom: HTMLElement
};

export function init (data : initProp) {
    ReactDOM.render(<ShareFrame
        onSetShareData={ data.onSetShareData }
        settingPageUrl={ data.settingPageUrl }
        serverUrl={ data.serverUrl }
    />, data.dom);
}