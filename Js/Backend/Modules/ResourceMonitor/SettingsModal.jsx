import React from 'react';
import _ from 'lodash';
import Webiny from 'webiny';

/**
 * @i18n.namespace SystemMonitor.Backend.ResourceMonitor.SettingsModal
 */
class SettingsModal extends Webiny.Ui.ModalComponent {
    renderDialog() {
        const reasonProps = {
            name: 'trigger',
            placeholder: this.i18n('Alarm trigger'),
            options: {
                cpu: this.i18n('CPU reaches'),
                memory: this.i18n('Memory reaches'),
                disk: this.i18n('Disk reaches'),
                load: this.i18n('Last 15 min load reaches')
            }
        };

        const {Modal, Settings, Form, Section, Alert, Dynamic, Grid, Select, Input, Button, Checkbox, Logic, ButtonGroup} = this.props;

        return (
            <Modal.Dialog>
                <Settings api="/entities/system-monitor/settings" onSubmitSuccess={this.hide}>
                    {({model, form}) => (
                        <Modal.Content>
                            <Form.Loader/>
                            <Modal.Header title={this.i18n('System Monitor Alarms')}/>
                            <Modal.Body>
                                <Alert type="info">
                                    {this.i18n('Recurring alarms will only send a notification once every 15 minutes.')}
                                </Alert>
                                <Section title={this.i18n('Alarms')}/>
                                <Dynamic.Fieldset name="alarms">
                                    <Dynamic.Row>
                                        {({data, actions}) => {
                                            return (
                                                <Grid.Row>π
                                                    <Grid.Col all={5}>
                                                        <Select {...reasonProps} validate="required"/>
                                                    </Grid.Col>
                                                    <Grid.Col all={4}>
                                                        <Input placeholder={this.i18n('Threshold')} name="threshold" validate="required"/>
                                                    </Grid.Col>
                                                    <Grid.Col all={3}>
                                                        <ButtonGroup>
                                                            <Button type="primary" label={this.i18n('Add')} onClick={actions.add(data)}/>
                                                            <Button type="secondary" label={this.i18n('x')} onClick={actions.remove(data)}/>
                                                        </ButtonGroup>
                                                    </Grid.Col>
                                                </Grid.Row>
                                            );
                                        }}
                                    </Dynamic.Row>
                                    <Dynamic.Empty>
                                        {({actions}) => {
                                            return (
                                                <Grid.Row>
                                                    <Grid.Col all={12}>
                                                        <h5>{this.i18n(`You have not yet created any alarms. Start by clicking "Add alarm"!`)}</h5>
                                                        <Button type="primary" label={this.i18n('Add alarm')} onClick={actions.add()}/>
                                                    </Grid.Col>
                                                </Grid.Row>
                                            );
                                        }}
                                    </Dynamic.Empty>
                                </Dynamic.Fieldset>

                                <Section title={this.i18n('Notifications')}/>
                                <Grid.Row>
                                    <Checkbox label={this.i18n('Send message to Slack')} name="slack"/>
                                    <Logic.Hide if={!_.get(model.settings, 'slack')}>
                                        <Grid.Col all={10} xsOffset={1}>
                                            <Input
                                                label={this.i18n('Slack Token')}
                                                name="token"
                                                validate="required"
                                                description={(
                                                    <span>
                                                        {this.i18n('Bot token to use when sending notifications.')}
                                                        <a target="_blank" href="https://api.slack.com/bot-users">{this.i18n('Create your Slack bot here.')}</a>
                                                    </span>
                                                )}/>
                                            <Grid.Row>
                                                <Grid.Col all={4}>
                                                    <Input label={this.i18n('Team')} name="team" validate="required"/>
                                                </Grid.Col>
                                                <Grid.Col all={4}>
                                                    <Input label={this.i18n('Channel')} name="channel" validate="required"/>
                                                </Grid.Col>
                                                <Grid.Col all={4}>
                                                    <Input label={this.i18n('Username')} name="username" validate="required"/>
                                                </Grid.Col>
                                            </Grid.Row>
                                        </Grid.Col>
                                    </Logic.Hide>
                                    <Checkbox label={this.i18n('Send email')} name="email"/>
                                    <Logic.Hide if={!_.get(model.settings, 'email')}>
                                        <Grid.Row>
                                            <Grid.Col all={10} xsOffset={1}>
                                                <Input label={this.i18n('Emails')} name="emails" validate="required"/>
                                            </Grid.Col>
                                        </Grid.Row>
                                    </Logic.Hide>
                                </Grid.Row>
                            </Modal.Body>
                            <Modal.Footer>
                                <Button type="default" label={this.i18n('Close')} onClick={this.hide}/>
                                <Button type="primary" label={this.i18n('Save')} onClick={form.submit}/>
                            </Modal.Footer>
                        </Modal.Content>
                    )}
                </Settings>
            </Modal.Dialog>
        );
    }
}

export default Webiny.createComponent(SettingsModal, {
    modules: ['Modal', 'Settings', 'Form', 'Section', 'Alert', 'Dynamic', 'Grid', 'Select', 'Input', 'Button', 'Checkbox', 'Logic', 'ButtonGroup']
});