(function () {

    var config = {
        postUrl: location.href,
        serverUrl: '<serverUrl>'
    };

    function initShare () {
        initButton();
    }

    function initButton () {
        var html = getTemplate();
        var fragment = document.createElement('div');
        fragment.innerHTML = html;

        fragment.querySelector('#share-to-wiznote').addEventListener('click', function () {
            var statusEl = document.querySelector('#share-to-wiznote-status');
            statusEl.style.display = '';

            doShare(config.serverUrl, {
                postUrl: config.postUrl
            }).then(function (res) {
                res.json().then(function (data) {
                    alert(JSON.stringify(data, null, 4));
                }).catch(function (data) {
                    alert(JSON.stringify(data, null, 4));
                });
            }).catch(function (data) {
                alert('share failed! \n' + JSON.stringify(data, null, 4));
            }).finally(function () {
                statusEl.style.display = 'none';
            });
        });

        function initFn () {
            var el = document.querySelector('.jss157 .MuiToolbar-root.MuiToolbar-regular');

            if (el) {
                el.append(
                    fragment.children[0]
                );
            }

            return el;
        }

        function taskFn () {
            setTimeout(function () {
                if (initFn()) {
                    return;
                }

                taskFn();
            }, 100)
        }

        taskFn();

    }

    function getTemplate () {
        var fn = function () {
            /*
                <div class="jss226 jss231">
                    <button id="share-to-wiznote" class="MuiButtonBase-root MuiButton-root jss232 jss234 jss252 jss172 MuiButton-contained" type="button">
                        <span class="MuiButton-label">
                            <span>分享到 Typecho<span id="share-to-wiznote-status" style="display: none;">(ing...)</span></span>
                        </span>
                    </button>
                </div>
            */
        };

        return fn.toString().match(/<div\s*[\s\S]*<\/div>/)[0];
    }

    function doShare (url, data) {
        return fetch(url,{
            method: 'POST',
            mode: (new URL(url).host === new URL(location.href).host ? undefined : 'cors'),
            headers: {
                'Content-Type': 'application/json'
            },
            body:JSON.stringify(data)
        })
    }

    initShare();

} ());