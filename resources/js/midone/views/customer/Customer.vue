<template>
    <AlertPlaceholder :messages="alertErrors" />
    <div class="intro-y" v-if="mode === 'list'">
        <DataList :title="t('views.customer.table.title')" :data="customerList" v-on:createNew="createNew" v-on:dataListChange="onDataListChange" :enableSearch="true">
           <template v-slot:table="tableProps">
                <table class="table table-report -mt-2">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">{{ t('views.customer.table.cols.code') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.customer.table.cols.name') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.customer.table.cols.is_member') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.customer.table.cols.customer_group_id') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.customer.table.cols.remarks') }}</th>
                            <th class="whitespace-nowrap">{{ t('views.customer.table.cols.status') }}</th>
                            <th class="whitespace-nowrap"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="tableProps.dataList !== undefined" v-for="(item, itemIdx) in tableProps.dataList.data">
                            <tr class="intro-x">
                                <td>{{ item.code }}</td>
                                <td><a href="" @click.prevent="toggleDetail(itemIdx)" class="hover:animate-pulse">{{ item.name }}</a></td>
                                <td>{{ item.remarks }}</td>
                                <td>
                                    <CheckCircleIcon v-if="item.is_member === 'ACTIVE'" />
                                    <XIcon v-if="item.is_member === 'INACTIVE'" />
                                </td>
                                <td>{{ item.customer_group.name }}</td>
                                <td>
                                    <CheckCircleIcon v-if="item.status === 'ACTIVE'" />
                                    <XIcon v-if="item.status === 'INACTIVE'" />
                                </td>
                                <td class="table-report__action w-12">
                                    <div class="flex justify-center items-center">
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.view')" @click.prevent="showSelected(itemIdx)">
                                            <InfoIcon class="w-4 h-4" />
                                        </Tippy>
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.edit')" @click.prevent="editSelected(itemIdx)">
                                            <CheckSquareIcon class="w-4 h-4" />
                                        </Tippy>
                                        <Tippy tag="a" href="javascript:;" class="tooltip p-2 hover:border" :content="t('components.data-list.delete')" @click.prevent="deleteSelected(itemIdx)">
                                            <Trash2Icon class="w-4 h-4 text-danger" />
                                        </Tippy>
                                    </div>
                                </td>
                            </tr>
                            <tr :class="{'intro-x':true, 'hidden transition-all': expandDetail !== itemIdx}">
                                <td colspan="6">
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.code') }}</div>
                                        <div class="flex-1">{{ item.code }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.name') }}</div>
                                        <div class="flex-1">{{ item.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.is_member') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.is_member === 'ACTIVE'">{{ t('components.dropdown.values.is_memberDDL.active') }}</span>
                                            <span v-if="item.is_member === 'INACTIVE'">{{ t('components.dropdown.values.is_memberDDL.inactive') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.customer_group_id') }}</div>
                                        <div class="flex-1">{{ item.customer_group.name }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.remarks') }}</div>
                                        <div class="flex-1">{{ item.remarks }}</div>
                                    </div>
                                    <div class="flex flex-row">
                                        <div class="ml-5 w-48 text-right pr-5">{{ t('views.customer.fields.status') }}</div>
                                        <div class="flex-1">
                                            <span v-if="item.status === 'ACTIVE'">{{ t('components.dropdown.values.statusDDL.active') }}</span>
                                            <span v-if="item.status === 'INACTIVE'">{{ t('components.dropdown.values.statusDDL.inactive') }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <Modal :show="deleteModalShow" @hidden="deleteModalShow = false">
                    <ModalBody class="p-0">
                        <div class="p-5 text-center">
                            <XCircleIcon class="w-16 h-16 text-danger mx-auto mt-3" />
                            <div class="text-3xl mt-5">{{ t('components.data-list.delete_confirmation.title') }}</div>
                            <div class="text-slate-600 mt-2">
                                {{ t('components.data-list.delete_confirmation.desc_1') }}<br />{{ t('components.data-list.delete_confirmation.desc_2') }}
                            </div>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" class="btn btn-outline-secondary w-24 mr-1" @click="deleteModalShow = false">
                                {{ t('components.buttons.cancel') }}
                            </button>
                            <button type="button" class="btn btn-danger w-24" @click="confirmDelete">{{ t('components.buttons.delete') }}</button>
                        </div>
                    </ModalBody>
                </Modal>
            </template>
        </DataList>
    </div>

    <div class="intro-y box" v-if="mode !== 'list'">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'create'">{{ t('views.customer.actions.create') }}</h2>
            <h2 class="font-medium text-base mr-auto" v-if="mode === 'edit'">{{ t('views.customer.actions.edit') }}</h2>
        </div>
        <div class="loader-container">
            <VeeForm id="customerForm" class="p-5" @submit="onSubmit" @invalid-submit="invalidSubmit" v-slot="{ handleReset, errors }">
                <div class="p-5">
                    <!-- #region Code -->
                    <div class="mb-3">
                        <label for="inputCode" class="form-label">{{ t('views.customer.fields.code') }}</label>
                        <div class="flex items-center">
                            <VeeField id="inputCode" name="code" type="text" :class="{'form-control':true, 'border-danger': errors['code']}" :placeholder="t('views.customer.fields.code')" :label="t('views.customer.fields.code')" rules="required" @blur="reValidate(errors)" v-model="customer.code" :readonly="customer.code === '[AUTO]'" />
                            <button type="button" class="btn btn-secondary mx-1" @click="generateCode" v-show="mode === 'create'">{{ t('components.buttons.auto') }}</button>
                        </div>
                        <ErrorMessage name="code" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Name -->
                    <div class="mb-3">
                        <label for="inputName" class="form-label">{{ t('views.customer.fields.name') }}</label>
                        <VeeField id="inputName" name="name" type="text" :class="{'form-control':true, 'border-danger': errors['name']}" :placeholder="t('views.customer.fields.name')" :label="t('views.customer.fields.name')" rules="required" @blur="reValidate(errors)" v-model="customer.name" />
                        <ErrorMessage name="name" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Is Member -->
                    <div class="mb-3">
                        <label for="is_member" class="form-label">{{ t('views.customer.fields.is_member') }}</label>
                        <VeeField as="select" id="is_member" name="is_member" :class="{'form-control form-select':true, 'border-danger': errors['is_member']}" v-model="customer.is_member" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in is_memberDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </VeeField>
                        <ErrorMessage name="is_member" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Customer Group -->
                    <div class="mb-3">
                        <label class="form-label" for="inputCustomerGroup">{{ t('views.customer.fields.customer_group_id') }}</label>
                        
                        <VeeField as="select" id="customer_group_id" name="customer_group_id" :class="{'form-control form-select':true, 'border-danger': errors['customer_group_id']}" v-model="customer.company.hId" :label="t('views.customer.fields.customer_group_id')" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in companyDDL" :value="c.hId">{{ c.name }}</option>
                        </VeeField>
                        <ErrorMessage name="customer_group_id" class="text-danger" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Sales Territory -->
                    <div class="mb-3">
                        <label for="inputSalesTerritory" class="form-label">{{ t('views.customer.fields.sales_territory') }}</label>
                        <input id="inputSalesTerritory" name="sales_territory" type="text" class="form-control" :placeholder="t('views.customer.fields.sales_territory')" v-model="customer.sales_territory" />
                    </div>
                    <!--  #endregion -->

                    <!-- #region Max Open Invoice -->
                    <div class="mb-3">
                        <label for="inputMaxOpenInvoice" class="form-label">{{ t('views.customer.fields.max_open_invoice') }}</label>
                        <input id="inputMaxOpenInvoice" name="max_open_invoice" type="text" class="form-control" :placeholder="t('views.customer.fields.max_open_invoice')" v-model="customer.max_open_invoice" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Max Outstanding Invoice -->
                    <div class="mb-3">
                        <label for="inputMaxOutstandingInvoice" class="form-label">{{ t('views.customer.fields.max_outstanding_invoice') }}</label>
                        <input id="inputMaxOutstandingInvoice" name="max_outstanding_invoice" type="text" class="form-control" :placeholder="t('views.customer.fields.max_outstanding_invoice')" v-model="customer.max_outstanding_invoice" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Max Invoice Age -->
                    <div class="mb-3">
                        <label for="inputMaxInvoiceAge" class="form-label">{{ t('views.customer.fields.max_invoice_age') }}</label>
                        <input id="inputMaxInvoiceAge" name="max_invoice_age" type="text" class="form-control" :placeholder="t('views.customer.fields.max_invoice_age')" v-model="customer.max_invoice_age" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Tax Id -->
                    <div class="mb-3">
                        <label for="inputTaxId" class="form-label">{{ t('views.customer.fields.tax_id') }}</label>
                        <input id="inputTaxId" name="tax_id" type="text" class="form-control" :placeholder="t('views.customer.fields.tax_id')" v-model="customer.tax_id" />
                    </div>
                    <!-- #endregion -->

                    <!-- #region Remarks -->
                    <div class="mb-3">
                        <label for="inputRemarks" class="form-label">{{ t('views.customer.fields.remarks') }}</label>
                        <textarea id="inputRemarks" name="remarks" type="text" class="form-control" :placeholder="t('views.customer.fields.remarks')" v-model="customer.remarks" rows="3"></textarea>
                    </div>
                    <!-- #endregion -->

                    <!-- #region Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">{{ t('views.customer.fields.status') }}</label>
                        <VeeField as="select" id="status" name="status" :class="{'form-control form-select':true, 'border-danger': errors['status']}" v-model="customer.status" rules="required" @blur="reValidate(errors)">
                            <option value="">{{ t('components.dropdown.placeholder') }}</option>
                            <option v-for="c in statusDDL" :key="c.code" :value="c.code">{{ t(c.name) }}</option>
                        </VeeField>
                        <ErrorMessage name="status" class="text-danger" />
                    </div>
                    <!-- #endregion -->
                </div>
                <div class="pl-5" v-if="mode === 'create' || mode === 'edit'">
                    <button type="submit" class="btn btn-primary w-24 mr-3">{{ t('components.buttons.save') }}</button>
                    <button type="button" class="btn btn-secondary" @click="handleReset(); resetAlertErrors()">{{ t('components.buttons.reset') }}</button>
                </div>
            </VeeForm>
            <div class="loader-overlay" v-if="loading"></div>
        </div>
        <hr/>
        <div>
            <button type="button" class="btn btn-secondary w-15 m-3" @click="backToList">{{ t('components.buttons.back') }}</button>
        </div>
    </div>
</template>

<script setup>
//#region Imports
import { onMounted, onUnmounted, ref, computed, watch } from "vue";
import axios from "@/axios";
import { useI18n } from "vue-i18n";
import { route } from "@/ziggy";
import dom from "@left4code/tw-starter/dist/js/dom";
import { useUserContextStore } from "@/stores/user-context";
import DataList from "@/global-components/data-list/Main";
import AlertPlaceholder from "@/global-components/alert-placeholder/Main";
import { getCachedDDL, setCachedDDL } from "@/mixins";
//#endregion

//#region Declarations
const { t } = useI18n();
//#endregion

//#region Data - Pinia
const userContextStore = useUserContextStore();
const selectedUserCompany = computed(() => userContextStore.selectedUserCompany );
//#endregion

//#region Data - UI
const mode = ref('list');
const loading = ref(false);
const alertErrors = ref([]);
const deleteId = ref('');
const deleteModalShow = ref(false);
const expandDetail = ref(null);
//#endregion

//#region Data - Views
const customerList = ref({});
const customer = ref({
    code: '',
    name: '',
    is_member: '',
    customer_group: { 
        hId: '',
        name: '' 
    },
    zone: '',
    max_open_invoice: '0',
    max_outstanding_invoice: '0',
    max_invoice_age: '0',
    payment_term: '0',
    customer_address: [
        {
            hId: '',
            address: '',
            city: '',
            contact: '',
            remarks: ''
        }
    ],
    tax_id: '',
    remarks: '',
    status: '1',
});
const is_memberDDL = ref([]);
const statusDDL = ref([]);
const customer_groupDDL = ref([]);
//#endregion

//#region onMounted
onMounted(() => {
    if (selectedUserCompany.value !== '') {
        getAllCustomers({ page: 1 });
        getDDLSync();
    } else  {
        
    }

    setMode();
    
    getDDL();

    loading.value = false;
});

onUnmounted(() => {
    sessionStorage.removeItem('DCSLAB_LAST_ENTITY');
});
//#endregion

//#region Methods
const setMode = () => {
    if (sessionStorage.getItem('DCSLAB_LAST_ENTITY') !== null) createNew();
}

const getAllCustomers = (args) => {
    customerList.value = {};
    if (args.pageSize === undefined) args.pageSize = 10;
    if (args.search === undefined) args.search = '';

    let companyId = selectedUserCompany.value;

    axios.get(route('api.get.db.customer.customer.read', { "companyId": companyId, "page": args.page, "perPage": args.pageSize, "search": args.search })).then(response => {
        customerList.value = response.data;
        loading.value = false;
    });
}

const getDDL = () => {
    if (getCachedDDL('is_memberDDL') == null) {
        axios.get(route('api.get.db.common.ddl.list.is_members')).then(response => {
            is_memberDDL.value = response.data;
            setCachedDDL('is_memberDDL', response.data);
        });    
    } else {
        is_memberDDL.value = getCachedDDL('is_memberDDL');
    }
    if (getCachedDDL('statusDDL') == null) {
        axios.get(route('api.get.db.common.ddl.list.statuses')).then(response => {
            statusDDL.value = response.data;
            setCachedDDL('statusDDL', response.data);
        });    
    } else {
        statusDDL.value = getCachedDDL('statusDDL');
    }
}

const getDDLSync = () => {
    axios.get(route('api.get.db.customer.customer_group.read.all_active', {
            companyId: selectedUserCompany.value,
            paginate: false
        })).then(response => {
            customer_groupDDL.value = response.data;
    });
}

const onSubmit = (values, actions) => {
    loading.value = true;

    var formData = new FormData(dom('#customerForm')[0]); 
    formData.append('company_id', selectedUserCompany.value);
    
    if (mode.value === 'create') {
        axios.post(route('api.post.db.customer.customer.save'), formData).then(response => {
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else if (mode.value === 'edit') {
        axios.post(route('api.post.db.customer.customer.edit', customer.value.hId), formData).then(response => {
            actions.resetForm();
            backToList();
        }).catch(e => {
            handleError(e, actions);
        }).finally(() => {
            loading.value = false;
        });
    } else { }
}

const handleError = (e, actions) => {
    //Laravel Validations
    if (e.response.data.errors !== undefined && Object.keys(e.response.data.errors).length > 0) {
        for (var key in e.response.data.errors) {
            for (var i = 0; i < e.response.data.errors[key].length; i++) {
                actions.setFieldError(key, e.response.data.errors[key][i]);
            }
        }
        alertErrors.value = e.response.data.errors;
    } else {
        //Catch From Controller
        alertErrors.value = {
            controller: e.response.status + ' ' + e.response.statusText +': ' + e.response.data.message
        };
    }
}

const invalidSubmit = (e) => {
    alertErrors.value = e.errors;
    if (dom('.border-danger').length !== 0) dom('.border-danger')[0].scrollIntoView({ behavior: "smooth" });
}

const reValidate = (errors) => {
    alertErrors.value = errors;
}

const emptyCustomer = () => {
    return {
        code: '[AUTO]',
        name: '',
        is_member: '',
        customer_group: { 
            hId: '',
            name: '' 
        },
        zone: '',
        max_open_invoice: '0',
        max_outstanding_invoice: '0',
        max_invoice_age: '0',
        payment_term: '0',
        customer_address: [
            {
                hId: '',
                address: '',
                city: '',
                contact: '',
                remarks: ''
            }
        ],
        tax_id: '',
        remarks: '',
        status: '1',
    }
}

const resetAlertErrors = () => {
    alertErrors.value = [];
}

const createNew = () => {
    mode.value = 'create';
    
    if (sessionStorage.getItem('DCSLAB_LAST_ENTITY') !== null) {
        customer.value = JSON.parse(sessionStorage.getItem('DCSLAB_LAST_ENTITY'));
        sessionStorage.removeItem('DCSLAB_LAST_ENTITY');
    } else {
        customer.value = emptyCustomer();
    }
    customer.value.company.hId = _.find(companyDDL.value, { 'hId': selectedUserCompany.value });
}

const onDataListChange = ({page, pageSize, search}) => {
    getAllCustomers({page, pageSize, search});
}

const editSelected = (index) => {
    mode.value = 'edit';
    customer.value = customerList.value.data[index];
}

const deleteSelected = (index) => {
    deleteId.value = customerList.value.data[index].hId;
    deleteModalShow.value = true;
}

const confirmDelete = () => {
    deleteModalShow.value = false;
    axios.post(route('api.post.db.customer.customer.delete', deleteId.value)).then(response => {
        backToList();
    }).catch(e => {
        alertErrors.value = e.response.data;
    }).finally(() => {

    });
}

const showSelected = (index) => {
    toggleDetail(index);
}

const backToList = () => {
    resetAlertErrors();
    sessionStorage.removeItem('DCSLAB_LAST_ENTITY');

    mode.value = 'list';
    getAllCustomers({ page: customerList.value.current_page, pageSize: customerList.value.per_page });
}

const toggleDetail = (idx) => {
    if (expandDetail.value === idx) {
        expandDetail.value = null;
    } else {
        expandDetail.value = idx;
    }
}

const generateCode = () => {
    if (customer.value.code === '[AUTO]') customer.value.code = '';
    else  customer.value.code = '[AUTO]'
}
//#endregion

//#region Computed
//#endregion

//#region Watcher
watch(selectedUserCompany, () => {
    if (selectedUserCompany.value !== '') {
        getAllCustomers({ page: 1 });
        getDDLSync();
    }
});

watch(customer, (newV) => {
    if (mode.value == 'create') sessionStorage.setItem('DCSLAB_LAST_ENTITY', JSON.stringify(newV));
}, { deep: true });
//#endregion
</script>