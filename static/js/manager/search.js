pimcore.registerNS('pimcore.plugin.manager.search');
pimcore.plugin.manager.search = Class.create({

    initialize: function() {
        this.getTabPanel();
    },

    activate: function() {
        var tabPanel = Ext.getCmp('pimcore_panel_tabs');
        tabPanel.activate('plugin_manager_search');
    },

    getTabPanel: function() {
        if (!this.panel) {
            this.panel = new Ext.Panel({
                id: 'plugin_manager_search',
                title: t('download_extension'),
                iconCls: 'pimcore_icon_plugin_add',
                border: false,
                layout: 'fit',
                closable: true,
                items: [this.getGrid()]
            });

            var tabPanel = Ext.getCmp('pimcore_panel_tabs');
            tabPanel.add(this.panel);
            tabPanel.setActiveItem('plugin_manager_search');

            this.panel.on('destroy', function() {
                pimcore.globalmanager.remove('plugin_manager_search');
            }.bind(this));

            pimcore.layout.refresh();
        }

        return this.panel;
    },

    getGrid: function() {
        this.store = new Ext.data.Store({
            restful: false,
            idProperty: 'id',
            remoteSort: true,
            fields : ['name', 'description', 'url', 'downloads', 'favers', 'repository'],
            proxy: {
                type: 'ajax',
                url: '/plugin/Manager',
                reader: {
                    type: 'json',
                    rootProperty : 'packages'
                }
            }
        });

        this.store.load();

        var typesColumns = [
            {
                header: t('name'),
                sortable: true,
                dataIndex: 'name',
                width : 250
            },
            {
                header: t('description'),
                id: 'extension_description',
                flex: 1,
                sortable: true,
                dataIndex: 'description'
            },
            {
                xtype: 'actioncolumn',
                width: 40,
                items: [{
                    tooltip: t('go_to_packagist'),
                    getClass: function() {
                        return 'pimcore_action_column manager_icon_link';
                    },
                    handler: function(grid, rowIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        window.open(rec.get('url'));
                    }.bind(this)
                }]
            },
            {
                header: t('downloads'),
                width: 80,
                sortable: true,
                dataIndex: 'downloads'
            },
            {
                xtype: 'actioncolumn',
                width: 40,
                items: [{
                    tooltip: t('download'),
                    getClass: function() {
                        return 'pimcore_action_column pimcore_icon_download';
                    },
                    handler: function(grid, rowIndex) {
                        var rec = grid.getStore().getAt(rowIndex);
                        this.openDownloadWindow(rec);
                    }.bind(this)
                }]
            }
        ];

        this.grid = new Ext.grid.GridPanel({
            frame: false,
            autoScroll: true,
            store: this.store,
            columns: typesColumns,
            autoExpandColumn: 'extension_description',
            trackMouseOver: true,
            columnLines: true,
            stripeRows: true,
            tbar: [{
                text: t('refresh'),
                iconCls: 'pimcore_icon_reload',
                handler: this.reload.bind(this)
            }],
            viewConfig: {
                forceFit: true
            }
        });

        return this.grid;
    },

    reload: function() {
        this.store.reload();
    },

    openDownloadWindow: function(rec) {
        this.downloadWindow = new Ext.Window({
            modal: true,
            title: t('plugin_manager_install'),
            width: 500,
            height: 250,
            layout: 'fit',
            closable: false,
            items: [{
                bodyStyle: 'padding: 10px;',
                autoScroll: true,
                html: ''
            }],
            buttons: [{
                text: t('close'),
                iconCls: 'pimcore_icon_apply',
                disabled: true,
                handler: function() {
                    this.downloadWindow.close();
                }.bind(this)
            }],
            listeners: {
                close: this.reload.bind(this)
            }
        });

        this.downloadWindow.show();

        this.downloadPrepare(rec);
    },

    downloadPrepare: function(rec) {
        Ext.Ajax.request({
            url: '/plugin/Manager/index/install',
            params: {
                name: rec.get('name')
            },
            success: this.downloadStarted.bind(this)
        });
    },

    downloadStarted: function(transport) {
        var info = Ext.decode(transport.responseText);

        if (info.success) {
            this.jobId = info.jobId;

            window.setTimeout(this.fetchStatus.bind(this), 1000);
        }
    },

    fetchStatus: function() {
        Ext.Ajax.request({
            url: '/plugin/Manager/index/status',
            params: {
                jobId: this.jobId
            },
            success: this.updateStatus.bind(this)
        });
    },

    updateStatus: function(transport) {
        var status = Ext.decode(transport.responseText);

        var log = this.downloadWindow.items.get(0);
        log.update(status.log);
        var d = log.body.dom;
        d.scrollTop = d.scrollHeight - d.offsetHeight;

        if (status.status == 'running') {
            window.setTimeout(this.fetchStatus.bind(this), 2000);
        } else {
            this.downloadWindow.getDockedItems()[1].items.items[0].enable();
        }
    }
});
