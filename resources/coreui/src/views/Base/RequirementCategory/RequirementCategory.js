import React, { Component } from 'react';
import {
  Alert,
  Row,
  Col,
  Card,
  CardHeader,
  CardBody,
  Table,
  Button,
  Modal, ModalHeader, ModalBody, ModalFooter,
  Form,
  FormGroup,
  FormText,
  Label,
  Input,
  InputGroup,
  InputGroupAddon

} from 'reactstrap';
import axios from 'axios';
import Paginations from "react-js-pagination";


class RequirementCategory extends Component {
  constructor(props) {
    super(props)
    this.state = {
      categories: [],
      users:[],

      add_form: false,
      id: '',
      name: '',
      file: null,
      updated_by: window.current_user_id,

      is_add_process_type: true,
      save_button_is_disabled: true,
      update_button_is_disabled: true,
      is_detail_page: false,

      has_alert_hidden: true,
      alert_type: 'danger',
      alert_message: '',

      active_page:1,
      items_count_per_page:1,
      total_items_count:1,
      current_index:'',

      search_loading: false,
      search_message: '',
      search_alert_type: 'primary',
      search_value:''
      
    }
    this.toggle_add_form = this.toggle_add_form.bind(this);
    this.handle_input_change = this.handle_input_change.bind(this);
    this.save_item = this.save_item.bind(this);
    this.update_item = this.update_item.bind(this);
    this.get_data = this.get_data.bind(this);
    this.check_inputs = this.check_inputs.bind(this);
    this.delete_item = this.delete_item.bind(this);
    this.disable_buttons = this.disable_buttons.bind(this);
    this.handle_page_change = this.handle_page_change.bind(this);
    this.handle_search_input_change = this.handle_search_input_change.bind(this);
    this.toggle_detail_page = this.toggle_detail_page.bind(this);
    this.go_back = this.go_back.bind(this);
    this.get_users = this.get_users.bind(this);
    this.get_user = this.get_user.bind(this);
    
  }

  go_back(){
    if (this.state.is_detail_page == true) {
      this.setState({is_detail_page:false})
    }
    
  }

  disable_buttons(){
    this.setState(
      {
        save_button_is_disabled: true,
        update_button_is_disabled: true,
      }
    ) 
  }
  enable_button(){

    if (this.state.is_add_process_type) {
      this.setState(
        {
          save_button_is_disabled: false,
          update_button_is_disabled: true,
        }
      ) 
    }else{
      this.setState(
        {
          save_button_is_disabled: true,
          update_button_is_disabled: false,
        }
      ) 
    }
    
  }

  check_inputs() {

    if (this.state.name =='' ) {
      console.log('Incomplete form values!')
      this.disable_buttons()
    }else{
      this.enable_button()
    }
  }

  delete_item(i) {
    var self = this;
    axios.delete('/api/requirements/categories/' + this.state.categories[i].id)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.setState({
          alert_message: 'Delete Successful.',
          alert_type: 'success',
          has_alert_hidden: false,
        })

      })
      .catch(function (error) {
        console.log(error);
        alert('Deletion Failed. Contact your System Administrator')
      });
  }

  async update_item(e) {
    e.preventDefault();
    
    var self = this;
    
    const form_data = new FormData();
    form_data.append('name',self.state.name);
    form_data.append('file',self.state.file);
    form_data.append('updated_by',window.current_user_id);

    
    await axios.post('/api/requirements/categories/' + this.state.id, form_data,{
      headers:{
        'Content-Type':'multipart/form-data'
      }
    })
    .then(function (response) {
        console.log(response);
        self.get_data()
        self.toggle_add_form()
        self.setState({
          alert_message: 'Update Successful.',
          alert_type: 'success',
          has_alert_hidden: false,
        })
      })
      .catch(function (error) {
        console.log(error);
        alert('Update Failed. Contact your System Administrator')
      });

  }

  load_item(i) {
    this.toggle_add_form();
    this.setState({
      id: this.state.categories[i].id,
      name: this.state.categories[i].name,
      file: this.state.categories[i].file,
      updated_by: window.current_user_id,
      update_button_is_disabled: true,

    })

    this.setState({ is_add_process_type: false });
  }

  async save_item(e) {
    e.preventDefault();
    
    var self = this;
    
    const form_data = new FormData();
    form_data.append('name',self.state.name);
    form_data.append('file',self.state.file);
    form_data.append('updated_by',window.current_user_id);

    
    await axios.post('/api/requirements/categories', form_data,{
      headers:{
        'Content-Type':'multipart/form-data'
      }
    })
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.toggle_add_form();
      })
      .catch(function (error) {
        console.log(error.response.status);
        if (error.response.data.message.includes('Requirement Category already')) {
          self.setState({
            alert_message: error.response.data.message,
            alert_type: 'danger',
            has_alert_hidden: false,
          })
        }else{
          alert('Save Failed. Contact your System Administrator')
        }
        

      });

  }
  handle_search_input_change(e) {
    // clear alert status
    var self = this;
    self.setState({
     search_message: '',
     search_alert_type: 'primary',
     has_alert_hidden: true,
     search_value: e.target.value
   })
   var value = 'xxxxxxxxxx';
   if(e.target.value){
    value = e.target.value
   
   axios.get('/api/requirements/categories/search/'+value)
      .then(function (response) {
        console.log(response);
        self.store_data_to_state(response.data)
      })
      .catch(function (error) {
        console.log(error);
        alert('Search Failed. Contact your System Administrator.')
      });
  
    }else{
      this.get_data()
    }
  }

  handle_input_change(e) {
     // clear alert status
    this.setState({
      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    })

    this.setState({ [e.target.name]: e.target.value });

    if (typeof e.target.files != undefined && e.target.files != null) {
      this.setState({ file: e.target.files[0] });
      
    }
    
    if (e.target.value == '') {
      this.disable_buttons();
      setTimeout(() => {
      
        this.check_inputs(e.target.name, e.target.value);
      }, 500);
    }else{
      this.check_inputs(e.target.name, e.target.value);
    }

  }

  toggle_add_form() {
    this.setState({
      id: '',
      name: '',
      file: '',
      updated_by: window.current_user_id,


      update_button_is_disabled: true,
      add_form: !this.state.add_form,
      is_add_process_type: true,

      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    });

  }
  toggle_detail_page(i=undefined){
    
    if (i >= 0 ) {
      this.setState({
        is_detail_page: true,
        current_index:i,
        id: this.state.categories[i].id,
        name: this.state.categories[i].name,
        file: this.state.categories[i].file,
        updated_by: window.current_user_id,

      });
    }else{
      this.setState({
        is_detail_page: false,
      });
    }
    
  }

  store_data_to_state(data) {
    // console.log(data)
    this.setState({ 
      categories: data.data,

      active_page: data.meta.current_page,
      items_count_per_page:data.meta.per_page,
      total_items_count:data.meta.total,
     })
  }

  get_data(page_number) {
    var self = this;
    axios.get('/api/requirements/categories/?page='+ page_number)
      .then(res => {
        self.store_data_to_state(res.data)
      }).catch(err => {
        console.log(err)
        alert('Server disconnected.')
      })
  }

  get_users() {
    var self = this;
    axios.get('/api/users/')
      .then(res => {
        self.setState({users:res.data.data})
      }).catch(err => {
        console.log(err)
        alert('Server disconnected.')
      })
  }

  get_user(id) {
    for (let i = 0; i < this.state.users.length; i++) {
      
      if (this.state.users[i].id == id) {
        return this.state.users[i].first_name + ' ' + this.state.users[i].last_name  
      }
      
      
    }
  }

  

  componentDidMount() {

    this.get_data()
    this.get_users()

  }

  handle_page_change(page_number) {
    console.log(`active page is ${page_number}`);
    this.get_data(page_number)
  }

  render() {
    return (
      <div className="animated fadeIn">
        <Row hidden={this.state.is_detail_page}>
          <Col xs="12" lg="12">
            <Card>
              <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-bank"></i> Requirement Category
                  </Col>
                  <Col xs="4" lg="4">
                  
                  <Input
                          type="text"
                          value={this.state.search_value}
                          
                          name="search"
                          placeholder="Search..."
                          onChange={this.handle_search_input_change}
                          />
                    </Col>
                    
                
                
                  <Col xs="4" lg="4">
                        
                        <Button className="float-lg-right" color="primary" onClick={this.toggle_add_form}><i className="fa fa-plus-circle"></i> Add</Button>
                        
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                <Modal isOpen={this.state.add_form} toggle={this.toggle_add_form}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggle_add_form}>{this.state.is_add_process_type ? 'Add New' : 'Update'} Requirement Category</ModalHeader>
                  <ModalBody>

                    <Row>
                      <Col xs="12" md="12">
                        <Card>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="name">Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.name} onChange={this.handle_input_change} type="text" id="name" name="name" placeholder="Text" />
                                  <FormText color="muted">Requirement Name</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="file">File</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input onChange={this.handle_input_change} type="file" id="customFile" name="customFile" />
                                  <FormText color="muted">Upload the template here</FormText>
                                </Col>
                              </FormGroup>
                              
                              
                              <Alert hidden={this.state.has_alert_hidden} color={this.state.alert_type}>
                                    {this.state.alert_message}
                                  </Alert>
                            </Form>

                          </CardBody>
                        </Card>
                      </Col>
                    </Row>

                  </ModalBody>
                  <ModalFooter>
                    <Button type='submit' hidden={!this.state.is_add_process_type} disabled={this.state.save_button_is_disabled} color="primary" onClick={this.save_item}><i className="fa fa-save"></i> Save</Button>{' '}
                    <Button type='submit' hidden={this.state.is_add_process_type} disabled={this.state.update_button_is_disabled} color="primary" onClick={this.update_item}><i className="fa fa-edit"></i>Update</Button>{' '}
                    <Button color="secondary" onClick={this.toggle_add_form}>Cancel</Button>
                  </ModalFooter>
                </Modal>
                
                <Alert hidden={this.state.has_alert_hidden} color={this.state.alert_type}>
                                    {this.state.alert_message}
                                  </Alert>
                <Table responsive>
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>File Link</th>
                      <th>Last Updated By</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {this.state.categories.map((data, i) => {
                      return (
                        <tr key={i}>
                          <td>{data.name}</td>
                          <td> <a className="btn btn btn-primary btn-sm" target="_blank" href={data.file}>Download</a></td>
                          <td>{this.get_user(data.updated_by)}</td>
                          <td>
                          <Button size="sm" className='text-white' color="info" onClick={() => this.toggle_detail_page(i)}><i className="fa fa-book"></i> Details</Button>
                            <Button size="sm" color="primary" onClick={() => this.load_item(i)}><i className="fa fa-pencil"></i></Button>
                            <Button size="sm" onClick={(e) => { if (window.confirm('Are you sure you wish to delete this item?')) this.delete_item(i) }} color="danger"><i className="fa fa-trash"></i></Button>
                          </td>
                        </tr>
                      )
                    })}


                  </tbody>
                </Table>
                <Paginations
                    activePage={this.state.active_page}
                    itemsCountPerPage={this.state.items_count_per_page}
                    totalItemsCount={this.state.total_items_count}
                    pageRangeDisplayed={5}
                    onChange={this.handle_page_change}
                />
                
              </CardBody>
            </Card>
          </Col>

        </Row>

        <Row hidden={!this.state.is_detail_page}>
                      <Col xs="12" md="12">
                        <Card>
                        <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-bank"></i> Requirement Category Detail
                  </Col>
                  <Col xs="4" lg="4">
                  
                  </Col>
                  <Col xs="4" lg="4">
                        
                        
                        <Button className="float-right" color="primary" onClick={this.go_back}><i className="fa fa-hand-o-left"></i> Back</Button>
                        
                  </Col>
                </Row>
              </CardHeader>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="name">Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Label>{this.state.name}</Label>
                                  
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="file">File Link</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <a className="btn btn btn-primary btn-sm" target="_blank" href={this.state.file}>Download</a>
                                  
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="iframe">View File</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <iframe src={this.state.file} width='100%' height='100%'></iframe>
                                </Col>
                              </FormGroup>
                              
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="updated_by">Last Updated By</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.get_user(this.state.updated_by)}</Label>
                                </Col>
                              </FormGroup>
                              
                              
                              
                            </Form>

                          </CardBody>
                        </Card>
                      </Col>
                    </Row>

                  
      </div>

    )
  }
}

export default RequirementCategory;
