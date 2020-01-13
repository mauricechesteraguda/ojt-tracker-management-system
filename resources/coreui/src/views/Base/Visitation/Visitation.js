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
  Badge

} from 'reactstrap';
import axios from 'axios';
// import Select from 'react-select';
import Paginations from "react-js-pagination";
// import UpdateModal from './UpdateModal';



class Visitation extends Component {
  constructor(props) {
    super(props)
    this.state = {
      companies: [],
      clusters: [],
      

      add_form: false,
      id: '',
      user_id: window.current_user_id,
      year: '',
      updated_by: window.current_user_id,

      is_add_process_type: true,
      save_button_is_disabled: true,
      update_button_is_disabled: true,
      is_detail_page: false,

      is_update_modal:false,
      update_modal_id:'',
      update_modal_year:'',
      update_modal_company_name:'',
      xupdate_button_is_disabled: true,
      date_visited:'',

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
    this.get_companies = this.get_companies.bind(this);
    this.go_back = this.go_back.bind(this);
    this.toggle_detail_page = this.toggle_detail_page.bind(this);
    
    this.xtoggle_add_form = this.xtoggle_add_form.bind(this);
    this.xcheck_inputs = this.xcheck_inputs.bind(this);
    this.xdisable_buttons = this.xdisable_buttons.bind(this);
    this.xenable_button = this.xenable_button.bind(this);
    this.xhandle_input_change = this.xhandle_input_change.bind(this);
  
  }

  xhandle_input_change(e) {
    // clear alert status
   this.setState({
     alert_message: '',
     alert_type: 'primary',
     has_alert_hidden: true,
   })

   this.setState({ [e.target.name]: e.target.value });
   
   
   this.xcheck_inputs(e.target.name, e.target.value);



 }

  xdisable_buttons(){
    this.setState(
      {
        xupdate_button_is_disabled: true,
      }
    ) 
  }
  xenable_button(){
    this.setState(
      {
        xupdate_button_is_disabled: false,
      }
    ) 
  }

  xcheck_inputs() {

    if (this.state.date_visited == '') {
      console.log('Incomplete form values!')
      setTimeout(() => {
        this.xdisable_buttons()
      }, 200);
      
    }else{
      setTimeout(() => {
        this.xenable_button()
      }, 200);
      
    }
  }

  xtoggle_add_form(e, id, year, company_name) {

    var self = this;

      self.setState({
        is_update_modal: !this.state.is_update_modal,
        update_modal_id: id,
        update_modal_year: year,
        update_modal_company_name: company_name,
      })

  }

  go_back(){
    if (this.state.is_detail_page == true) {
      this.setState({is_detail_page:false,
        companies:[]})
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

    if (this.state.year == '') {
      console.log('Incomplete form values!')
      this.disable_buttons()
    }else{
      this.enable_button()
    }
  }

  delete_item(i) {
    var self = this;
    axios.delete('api/clusters/' + this.state.clusters[i].id)
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

  update_item(e) {
    e.preventDefault();

    var self = this;
    let payload = {
      year: this.state.year,
      updated_by: this.state.updated_by,
    }
    axios.post('/api/clusters/' + this.state.id, payload)
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
      id: this.state.clusters[i].id,
      year: this.state.clusters[i].year,
      updated_by: this.state.clusters[i].updated_by,

      update_button_is_disabled: true,

    })

    this.setState({ is_add_process_type: false });
  }

  save_item(e) {
    e.preventDefault();
    
    var self = this;
    let payload = {
      user_id: window.current_user_id,
      year: this.state.year,
      updated_by: this.state.updated_by,
    }
    axios.post('/api/clusters', payload)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.toggle_add_form();
      })
      .catch(function (error) {
        console.log(error.response.status);
        if (error.response.data.message.includes('Visitation already')) {
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
   
   axios.get('/api/clusters/search/'+value)
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
    
    
    this.check_inputs(e.target.name, e.target.value);



  }

  toggle_add_form() {
    this.setState({
      id: '',
      user_id: window.current_user_id,
      year: '',
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
    var self = this;
    if (i >= 0 ) {
      this.setState({
        is_detail_page: true,
        current_index:i,
        id: this.state.clusters[i].id,
        year: this.state.clusters[i].year,
        updated_by: this.state.clusters[i].updated_by,
      });
      setTimeout(() => {
        self.get_companies()
      }, 500);
    }else{
      this.setState({
        is_detail_page: false,
      });
    }
    
  }

  store_data_to_state(data) {
    // console.log(data)
    this.setState({ 
      clusters: data.data,

      active_page: data.meta.current_page,
      items_count_per_page:data.meta.per_page,
      total_items_count:data.meta.total,
     })
  }

  get_data(page_number) {
    var self = this;
    axios.get('/api/clusters/?page='+ page_number)
      .then(res => {
        self.store_data_to_state(res.data)
      }).catch(err => {
        console.log(err)
        alert('Server disconnected.')
      })
  }
  
  get_companies() {
    // if (this.state.is_add_process_type) {
      var self = this;
      var cluster_id = this.state.clusters[this.state.current_index].id;
      axios.get('/api/companies/cluster/' + cluster_id)
        .then(res => {
          self.setState({companies:res.data.data})
        }).catch(err => {
          console.log(err)
          alert('Server disconnected.')
        })
    // }

  }

  componentDidMount() {

    this.get_data()

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
                  
                  <i className="fa fa-tasks"></i> Visitation
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
                        
                        <Button className="float-lg-right" color="primary" onClick={this.toggle_add_form}><i className="fa fa-gear"></i> Generate</Button>
                        
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                <Modal isOpen={this.state.add_form} toggle={this.toggle_add_form}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggle_add_form}>Generate Visitation by Year</ModalHeader>
                  <ModalBody>

                    <Row>
                      <Col xs="12" md="12">
                        <Card>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">

                            

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="year">Year</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.year} onChange={this.handle_input_change} type="select" name="year" id="year">
                                  <option value=""></option>
                                  <option value="2019">2019</option>
                                  <option value="2020">2020</option>
                                  <option value="2021">2021</option>
                                  <option value="2022">2022</option>
                                  <option value="2023">2023</option>
                                  <option value="2024">2024</option>
                                  <option value="2025">2025</option>
                                  <option value="2026">2026</option>
                                  <option value="2027">2027</option>
                                  <option value="2028">2028</option>
                                  <option value="2029">2029</option>
                                  <option value="2030">2030</option>
                                  

                                    </Input>
                                    <FormText>This will fetch all unscheduled internships for visitation</FormText>
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
                      <th>Cluster</th>
                      <th>Year</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {this.state.clusters.map((data, i) => {
                      return (
                        <tr key={i}>
                          <td>{data.year + '-' + data.id}</td>
                          <td>{data.year}</td>
                          {/* <td><Label><Badge color={data.is_approved == '1' ? 'success':'danger'} >{data.is_approved == '1' ? 'verified':'pending'}</Badge></Label></td> */}
                          <td></td>
                          <td>
                          <Button size="sm" className='text-white' color="info" onClick={() => this.toggle_detail_page(i)}><i className="fa fa-book"></i> View Companies</Button>
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
          <Col xs="12" lg="12">
            <Card>
              <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-tasks"></i> Companies by Cluster
                  </Col>
                  <Col xs="4" lg="4">
                  
                  </Col>
                  <Col xs="4" lg="4">
                        
                        
                        <Button className="float-right" color="primary" onClick={this.go_back}><i className="fa fa-hand-o-left"></i> Back</Button>
                        
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>        
              <Table responsive>
                  <thead>
                    <tr>
                      <th>Company</th>
                      <th>Address</th>
                      <th>Status</th>
                      <th>Date Visited</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {this.state.companies.map((data, i) => {
                      return (
                        <tr key={i}>
                          <td>{data.name}</td>
                          <td>{data.address}, {data.city},{data.province}, {data.country}</td>
                          {/* <td><Label><Badge color={data.is_approved == '1' ? 'success':'danger'} >{data.is_approved == '1' ? 'verified':'pending'}</Badge></Label></td> */}
                          <td></td>
                          <td>{data.date_visited}</td>
                          <td>
                          <Button size="sm" className='text-white' color="primary" onClick={(e)=> this.xtoggle_add_form(e,data.id,this.state.year,data.name +' - '+data.address+','+data.city+','+data.province+','+data.country)}><i className="fa fa-edit"></i> Update Status</Button>
                            </td>
                        </tr>
                      )
                    })}


                  </tbody>
                </Table>

                

                <Modal isOpen={this.state.is_update_modal} toggle={this.xtoggle_add_form}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.xtoggle_add_form}>Update Visitation to Company</ModalHeader>
                  <ModalBody>

                    <Row>
                      <Col xs="12" md="12">
                        <Card>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">

                            <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="company">Company</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Label >{this.state.update_modal_company_name}</Label>
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="year">Year</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Label >{this.state.update_modal_year}</Label>
                                </Col>
                              </FormGroup>

                            <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="date_visited">Date Visited</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.date_visited} onChange={this.xhandle_input_change} type="date" id="date_visited" name="date_visited" placeholder="Text" />
                                  <FormText color="muted">Input Date Visited</FormText>
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
                    
                    <Button type='submit' disabled={this.state.xupdate_button_is_disabled} color="primary" onClick={this.xupdate_item}><i className="fa fa-edit"></i>Update</Button>{' '}
                    <Button color="secondary" onClick={(e)=>this.xtoggle_add_form(e)}>Cancel</Button>
                  </ModalFooter>
                </Modal>
                
                          </CardBody>
                        
            </Card>
            
          </Col>

         

        </Row>
        
        
      </div>

    )
  }
}

export default Visitation;
