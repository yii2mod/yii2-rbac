var rbac = {
    init: function (options) {
        this._onSearch = false;
        this.name = options.name;
        this.route = options.route;
        this.routeAssign = options.routeAssign;
        this.routeDelete = options.routeDelete;
        this.routeSearch = options.routeSearch;
    },
    roleSearch: function () {
        if (!rbac._onSearch) {
            rbac._onSearch = true;
            var $this = $(this);
            setTimeout(function () {
                rbac._onSearch = false;
                var data = {
                    id: rbac.name,
                    target: $this.data('target'),
                    term: $this.val()
                };
                var target = '#' + $this.data('target');
                $.get(rbac.route, data, function (html) {
                    $(target).html(html);
                });
            }, 500);
        }
    },
    action: function () {
        var action = $(this).data('action');
        var params = $((action == 'assign' ? '#available' : '#assigned') + ', .role-search').serialize();
        var urlAssign = rbac.routeAssign;
        var urlDelete = rbac.routeDelete;
        $.post(action == 'assign' ? urlAssign : urlDelete,
            params, function (r) {
                $('#available').html(r[0]);
                $('#assigned').html(r[1]);
            }, 'json');
        return false;
    },
    refresh: function () {
        var refreshButton = $(this);
        refreshButton.button('loading');
        $.get(rbac.routeSearch, {
                target: 'available',
                term: $('input[name="search_av"]').val(),
                refresh: true
            },
            function (html) {
                $('#available').html(html);
                refreshButton.button('reset');
            }
        );
        return false;
    }
};

$(function () {
    $('.role-search').on('keydown', rbac.roleSearch);
    $('a[data-action]').on('click', rbac.action);
    $('#btn-refresh').on('click', rbac.refresh);
});