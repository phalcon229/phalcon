function Api()
{
    this.get = function(path, data, succ, complete, err) {
        this.base(path, 'GET', data, succ, complete, err);
    }

    this.post = function(path, data, succ, complete, err) {
        this.base(path, 'POST', data, succ, complete);
    }

    this.getHtml = function(path, data, succ, complete, err) {
        this.base(path, 'GET' , data, succ, complete, err, 'html');
    }

    this.base = function(path, method, data, succ, complete, error, dataType) {
        dataType = dataType || 'json';
        $.ajax({
            type: method,
            url: path,
            data: data,
            dataType: dataType,
            success: function(res) {
                if (res.code == 401) {
                    alert('身份认证失败，请重新登录')
                    window.location.href = '/auth/login'
                    return false;
                }

                if (res.code == 500 && res.msg) {
                    alert(res.msg);
                    return false;
                }

                typeof succ == "function" && succ(res)
            },
            error: function(err) {
                typeof error == "function" && error()
            },
            complete: function() {
                typeof complete == "function" && complete()
            }
        });
    }

}