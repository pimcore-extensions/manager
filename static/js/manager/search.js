
pimcore.registerNS("pimcore.plugin.manager.search");
pimcore.plugin.manager.search = Class.create({

    initialize: function () {
        this.getTabPanel();
    },

    activate: function () {
        var tabPanel = Ext.getCmp("pimcore_panel_tabs");
        tabPanel.activate("plugin_manager_search");
    },

    getTabPanel: function () {

        if (!this.panel) {
            this.panel = new Ext.Panel({
                id: "plugin_manager_search",
                title: t("download_extension"),
                iconCls: "pimcore_icon_plugin_add",
                border: false,
                layout: "fit",
                closable:true,
                items: [this.getGrid()]
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.activate("plugin_manager_search");


            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove("plugin_manager_search");
            }.bind(this));

            pimcore.layout.refresh();
        }

        return this.panel;
    },

    getGrid: function () {

        this.store = new Ext.data.JsonStore({
            id: 'plugin_extensions',
            url: '/plugin/Manager',
            restful: false,
            root: "packages",
            fields: ["name","description", "url", "downloads", "favers", "repository"]
        });
        this.store.load();

        var typesColumns = [
            {header: t("name"), width: 200, sortable: true, dataIndex: 'name'},
            {header: t("description"), id: "extension_description", width: 200, sortable: true, dataIndex: 'description'},
            {
                header: t('description'),
                xtype: 'actioncolumn',
                width: 30,
                items: [{
                    tooltip: t('description'),
                    getClass: function (v, meta, rec) {
                        return "pimcore_action_column pimcore_icon_layout_region";
                    },
                    handler: function (grid, rowIndex) {

                        var rec = grid.getStore().getAt(rowIndex);
                        window.open(rec.get("url"));

                    }.bind(this)
                }]
            },
            {
                header: t('download'),
                xtype: 'actioncolumn',
                width: 30,
                items: [{
                    tooltip: t('download'),
                    getClass: function (v, meta, rec) {
                        return "pimcore_action_column pimcore_icon_download";
                    },
                    handler: function (grid, rowIndex) {

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
			columns : typesColumns,
            autoExpandColumn: "extension_description",
            trackMouseOver: true,
            columnLines: true,
            stripeRows: true,
            tbar: [{
                text: t("refresh"),
                iconCls: "pimcore_icon_reload",
                handler: this.reload.bind(this)
            }],
            viewConfig: {
                forceFit: true
            }
        });

        return this.grid;
    },

    reload: function () {
        this.store.reload();
    },

    openDownloadWindow: function (rec) 
    {
        this.downloadWindow = new Ext.Window({
            modal: true,
            width: 500,
            height: 200,
            items: [],
            listeners: {
                close: this.reload.bind(this)
            }
        });

        this.downloadWindow.show();

        this.downloadPrepare(rec);
    },

    downloadPrepare: function (rec) {

        this.downloadWindow.removeAll();
        this.downloadWindow.add({
            bodyStyle: "padding:10px;",
            html: t("plugin_manager_install")
        });

        this.downloadWindow.doLayout();

        Ext.Ajax.request({
            url: "/plugin/Manager/index/install",
            params: {
                name: rec.get("name")
            },
            success: this.downloadStarted.bind(this)
        });
    },

    downloadStarted: function (transport) 
    {
        var updateInfo = Ext.decode(transport.responseText);
        var message;
        
        if(updateInfo.success)
        {
            message = t("plugin_manager_download_started");
            
            this.jobId = updateInfo.jobId;
            
            window.setTimeout(this.fetchStatus.bind(this), 5000);
        }
        else
            message = updateInfo.message;
    },
    
    fetchStatus : function()
    {
        Ext.Ajax.request({
            url: "/plugin/Manager/index/status",
            params: {
                jobId: this.jobId
            },
            success: this.newStatus.bind(this)
        });
    },
    
    newStatus : function(transport)
    {
        var status = Ext.decode(transport.responseText);
        
        if(status.status == "running")
        {
            window.setTimeout(this.fetchStatus.bind(this), 5000);
        }
        else
        {
            this.downloadFinished(status.logFile);
        }
    },
    
    downloadFinished: function(log)
    {
        this.downloadWindow.removeAll();
        this.downloadWindow.add({
            bodyStyle: "padding: 20px;",
            html: log,
            buttons: [{
                text: t("close"),
                iconCls: "pimcore_icon_apply",
                handler: function () {
                    this.downloadWindow.close();
                }.bind(this)
            }]
        });
        this.downloadWindow.doLayout();
    }
});