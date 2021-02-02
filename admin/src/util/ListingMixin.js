import Error from "./Error";
import {request} from "./Request";
import qs from "qs";
import {mapState} from "vuex";

const DEFAULT_PAGINATION_STATE = {
    current: 1,
    perPage: 10,
    total: 0,
    last_page: 0,
}

export default {
    data() {
        const {query} = this.$route
        const listQueryParams = {
            ...query,
            page: (query.page && query.page > 0) ? parseInt(query.page) : 1,
            perPage: (query.perPage && query.perPage > 0) ? query.perPage : DEFAULT_PAGINATION_STATE.perPage,
            sortOrder: (query.sortOrder) ? query.sortOrder : '',
            sortField: (query.sortField) ? query.sortField : '',
        }

        const pagination = {
            current: listQueryParams.page,
            perPage: listQueryParams.perPage,
            total: 0,
            last_page: 0,
        }

        return {
            dataSource: [],
            pagination: pagination,
            sortField: '',
            sortOrder: '',
            search: ((query.search) ? query.search : ''),
            formErrors: new Error({}),
            listingLoading: false,
            operation: (query.operation ? query.operation : null),
            listQueryParams: listQueryParams,
            listUrl: '',
            isFilterApplied: false,
            focusable: null
        }
    },
    mounted() {
        this.loadList(this.listQueryParams)
    },
    methods: {
        hasListAccess(permission) {
            return true;
        },
        async loadList(listQueryParams, applyFilter = false) {
            this.formErrors = new Error({})
            this.isFilterApplied = (this.isFilterApplied === 'reset') ? false : applyFilter

            if (!this.hasListAccess()) {
                return false;
            }

            const extraParams = this.getExtraParams();
            let newListQueryParams = (listQueryParams ? listQueryParams : this.listQueryParams)
            const params = {...newListQueryParams, ...extraParams}
            this.listingLoading = true
            try{
                const response = await request({
                    method: "get",
                    url: this.listUrl,
                    params: params,
                    paramsSerializer: ((params) => qs.stringify(params)),
                })

                const {data, meta} = response
                this.dataSource = data
                this.pagination = {
                    ...this.pagination,
                    current: parseInt(this.listQueryParams.page),
                    total: meta.total,
                    last_page: meta.last_page,
                }

                this.listLoaded()
            } catch(error) {
                this.dataSource = []
            } finally {
                this.listingLoading = false
            }
        },
        listLoaded() {
            // Write something after list loaded
        },
        handleTableChange(pagination, filters, sorter) { // For ant Design table
            this.handleSort(sorter.field, (sorter.order === 'descend' ? 'desc' : 'asc'))
        },
        handleSortChange({sortBy, sortDesc}) { // For bootstrap table
            this.handleSort(sortBy, (sortDesc===true ? 'desc' : 'asc'))
        },
        handleSearch() {
            let listQueryParams = this.listQueryParams
            if (this.search !== '') {
                listQueryParams['search'] = this.search
                listQueryParams['page'] = 1;
            } else {
                delete listQueryParams['search']
            }

            this.$router.replace({query: listQueryParams}).catch(() => {
            })
        },
        handleSort(key, active) {
            let _key = ''
            let _active = ''
            if (key && active) {
                _key = key
                _active = active
            }

            if (this.sortField === _key && this.sortOrder === _active) {
                this.sortField = '';
                this.sortOrder = '';
                return
            }

            const listQueryParams = {
                ...this.listQueryParams,
                sortField: _key,
                sortOrder: _active,
            }

            this.listQueryParams = listQueryParams

            this.sortField = _key
            this.sortOrder = _active
            this.$router.replace({query: Object.assign({}, this.$route.query, listQueryParams)}).catch(() => {
            })
        },
        getExtraParams() {
            return {}
        },
        handleQueryChange(to) {
            this.operation = ((to.operation) ? to.operation : null)

            if ((to.operation === 'edit' || to.operation === 'detail') && to.oToken) {
                    this.handleEditClick(to.oToken);
                return
            }

            // When you double click on left sidebar then it will not appear
            // so to fix that adding page on url
            const {page} = to;
            to = {...to, page: (page ? page : 1)}
            this.loadList(to)
        },
        replaceQuery(listQueryParams) {
            delete listQueryParams['operation']
            delete listQueryParams['oToken']
            this.listQueryParams = listQueryParams;
            this.$router.replace({query: Object.assign({}, this.$route.query, listQueryParams)}).catch(() => {})
        },
        handleOperationClose() {
            this.operation = null
            this.operationTitle = 'All'
            this.formErrors = new Error({})
            const listQueryParams = Object.assign({}, this.$route.query)
            delete listQueryParams['operation']
            delete listQueryParams['oToken']
            this.listQueryParams = {...this.listQueryParams, ...listQueryParams}
            this.$router.replace({query: listQueryParams}).catch(() => {
            })
            this.afterCloseOperation()
        },
        afterCloseOperation() { },
        handleResetClick() {
            let listQueryParams = this.listQueryParams
            this.search = ''
            this.sortField = ''
            this.sortOrder = ''
            delete listQueryParams['search']
            delete listQueryParams['sortField']
            delete listQueryParams['sortOrder']
            delete listQueryParams['filters']
            this.pagination.perPage = 10
            this.listQueryParams = listQueryParams
            this.$router.replace({query: listQueryParams}).catch(() => {
            })
            this.handleAfterResetEvent()
        },
        handleAfterResetEvent() {

        },
        setFocus() {
            if(this.focusable) {
                this.$refs[this.focusable].focus();
            }
        },
    },
    computed: {
        ...mapState(['settings']),
    },
    watch: {
        'pagination.current': function (newVal) {
            const currentPage = (newVal ? newVal : 1)
            const listQueryParams = {
                ...this.listQueryParams,
                page: parseInt(currentPage)
            }

            this.replaceQuery(listQueryParams)
        },
        'pagination.perPage': function (newVal) {
            const perPage = (newVal ? newVal : 10)
            const listQueryParams = {
                ...this.listQueryParams,
                perPage: parseInt(perPage)
            }

            this.replaceQuery(listQueryParams)
        },
        '$route.query': function (to, from) {
            this.handleQueryChange(to);
        },
    }
};
